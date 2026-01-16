<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/User.php';
require_once $routePath . 'services/user.mailer.php';
require_once $routePath . 'services/user.green.php';

configureCORS();
loadVarEnv();
header("Content-Type: application/json");

// Create a database connection
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$sendmailer = new UserMailer($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_USERNAME'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);
$sendgreen = new UserGreen($_ENV['API_GREEN_INSTANCE'], $_ENV['API_GREEN_TOKEN'], $_ENV['API_GREEN_IND']);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return trim($data);
}

if ($requestMethod && $requestMethod == 'POST') {
    $action = isset($_GET['action']) ? sanitize_input($_GET['action']) : null;
    switch ($action) {
        case 'code':
            $email = sanitize_input($_POST['email']);
            if (empty($email)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez entrer votre email !!!'));
                exit();
            }

            $user->email = $email;
            $result = $user->readByEmail();
            if ($result) {
                $code = rand(100000, 999999);
                $user->createCodeVerify($code);
                
                $sendmailer->sendPasswordReset($email, $result['username'], $code);
                $sendgreen->sendPasswordCode($result['phone'], $result['username'], $code);

                $data_result = array('username' => $result['username'], 'email' => $email, 'code' => $code);
                echo json_encode(array('status' => 'success', 'message' => 'Données utilisateur', 'data' => $data_result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun utilisateur trouvé avec cet identifiant.'));
            }
            break;

        case 'reset':
            $email = sanitize_input($_POST['email']);
            $code = sanitize_input($_POST['code']);
            $password = sanitize_input($_POST['password']);

            if (empty($code) || empty($email) || empty($password)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            $user->email = $email;
            $code_db = $user->readCodeVerify($code);
            if (!$code_db) {
                echo json_encode(array('status' => 'danger', 'message' => 'Code invalide.'));
                exit();
            }

            $result = $user->readByEmail();
            if ($user->resetPassword($result['username'], $password)) {
                $user->deleteCodeVerify();
                echo json_encode(array('status' => 'success', 'message' => 'Mot de passe modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du mot de passe.'));
            }
            break;

        default:
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
            break;
    }
} else {
    echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
}

// Close database connection
$db = null;
exit();
