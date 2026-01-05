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

        $user->username = $username;
        $user_data = $user->readByUsername();
        $token = JWTUtils::generateJWT(['user_id' => $user_data['id'], 'username' => $user_data['username'], 'role' => $user_data['role_id']]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Connecté avec succès',
            'token' => $token,
        ]);

        $connexion->user_id = (int) $user_data['id'];
        $connexion->token = substr($token, 0, 255);
        $connexion->ip = substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 50);
        $connexion->user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255);
        $connexion->create();

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

$db = null;
exit();
