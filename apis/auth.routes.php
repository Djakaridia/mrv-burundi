<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'models/User.php';
require_once $routePath . 'models/Connexion.php';
require_once $routePath . 'services/user.mailer.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$connexion = new Connexion($db);

loadVarEnv();
$sendmailer = new UserMailer($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_USERNAME'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Identifiants manquants']);
        exit();
    }

    if ($user->authenticate($username, $password)) {
        session_unset();

        // Récupérer l'utilisateur depuis la base de données et générer un token JWT
        $user->username = $username;
        $user_data = $user->readByUsername();
        $token = JWTUtils::generateJWT([
            'user_id' => $user_data['id'], 
            'username' => $user_data['username'], 
            'role' => $user_data['role_id'],
            'domain' => $_SERVER['HTTP_HOST'],
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Connecté avec succès',
            'token' => $token,
            'data' => ['id' => $user_data['id'], 'username' => $user_data['username'], 'role' => $user_data['role_id']]
        ]);

        // Enregistrer la connexion
        $connexion->user_id = (int) $user_data['id'];
        $connexion->token = substr($token, 0, 255);
        $connexion->ip = substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 50);
        $connexion->user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255);
        $connexion->create();

        // Envoyer un email de connexion
        $sendmailer->sendLogin($user_data['email'], $user_data['nom'], date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']);
        exit();
    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Identifiant ou mot de passe incorrect.']);
        exit();
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée.']);
    exit();
}

// Close database connection
$db = null;
exit();
