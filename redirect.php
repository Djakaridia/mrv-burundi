<?php
require_once __DIR__ . '/config/jwt-token.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

// Configuration sécurisée de la session
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

try {
    // Vérifier le token dans l'URL (nettoyé)
    $token = isset($_GET['tk']) ? trim($_GET['tk']) : null;
    $remember = isset($_GET['remember']) ? (int)$_GET['remember'] : 0;

    // Vérifier dans les cookies HttpOnly
    if (!$token && isset($_COOKIE['authtkmrv'])) {
        $token = trim($_COOKIE['authtkmrv']);
    }

    // Vérifier dans les headers Authorization
    if (!$token && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = trim($_SERVER['HTTP_AUTHORIZATION']);
        if (preg_match('/Bearer\s([a-zA-Z0-9\-_.]+)$/', $authHeader, $matches)) {
            $token = $matches[1];
        }
    }

    // Vérifier l'existence du token
    if (!$token) {
        http_response_code(401);
        header('Location: logout.php?error=token_manquant');
        exit();
    }

    // Validation stricte du format JWT avant traitement
    if (!preg_match('/^[a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_]+$/', $token)) {
        http_response_code(401);
        header('Location: logout.php?error=token_invalide');
        exit();
    }

    // Vérifier la validité du token
    $payload = JWTUtils::validateJWT($token);
    if ($payload['domain'] !== $_SERVER['HTTP_HOST']) {
        http_response_code(403);
        header('Location: logout.php?error=domaine_interdit');
        exit();
    }

    // Vérifier l'existence de l'utilisateur
    $user->id = $payload['user_id'];
    $user_data = $user->readById();
    if (!$user_data || $user_data['validity'] !== 1) {
        http_response_code(401);
        header('Location: logout.php?error=compte_invalide');
        exit();
    }

    // Regénérer l'ID de session
    session_regenerate_id(true);
    $_SESSION = [
        'user-data' => [
            'user-id' => $user_data['id'],
            'user-username' => $user_data['username'],
            'user-role' => $user_data['role_id'],
            'user-nom' => $user_data['nom'],
            'user-prenom' => $user_data['prenom'],
            'user-email' => $user_data['email'],
            'user-phone' => $user_data['phone'],
            'user-validity' => $user_data['validity'],
            'last_activity' => time()
        ],
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'csp_nonce' => bin2hex(random_bytes(16))
    ];

    // Configurer le cookie sécurisé
    $cookieParams = [
        'expires' => $remember ? time() + 86400 * 30 : time() + 86400,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ];
    setcookie('authtkmrv', $token, $cookieParams);

    // Redirection sécurisée
    header('Content-Type: text/html; charset=utf-8');
    header("Content-Security-Policy: script-src 'self' 'nonce-{$_SESSION['csp_nonce']}'");
    echo <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <title>Redirection</title>
            <script nonce="{$_SESSION['csp_nonce']}">
                try {
                    localStorage.setItem('authtkmrv', '{$token}');
                    window.location.href = 'accueil.php';
                    window.history.replaceState({}, document.title, 'accueil.php');
                } catch(e) {
                    console.error("Erreur", e);
                    window.location.href = 'accueil.php';
                    window.history.replaceState({}, document.title, 'accueil.php');
                }
            </script>
        </head>
        <body>
            <noscript><p>Redirection en cours... <a href="accueil.php">Cliquez ici</a></p></noscript>
        </body>
        </html>
        HTML;
    exit();
} catch (Exception $e) {
    http_response_code(401);
    header('Location: logout.php?error=' . urlencode($e->getMessage()));
    exit();
}
