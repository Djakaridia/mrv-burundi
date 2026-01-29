<?php
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

// Authentication
try {
    $jwt = JWTUtils::getBearerToken();
    $payload = JWTUtils::validateJWT($jwt);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['status' => 'danger', 'message' => 'Accès non autorisé']);
    exit();
}

// Create a database connection
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$sendmailer = new UserMailer($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_USERNAME'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);
$sendgreen = new UserGreen($_ENV['API_GREEN_INSTANCE'], $_ENV['API_GREEN_TOKEN'], $_ENV['API_GREEN_IND']);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id === null) {
            $user->nom = sanitize_input($_POST['nom']);
            $user->prenom = sanitize_input($_POST['prenom']);
            $user->username = sanitize_input($_POST['username']);
            $user->email = sanitize_input($_POST['email']);
            $user->phone = sanitize_input($_POST['phone']);
            $user->password = sanitize_input($_POST['password']);
            $user->role_id = sanitize_input($_POST['role_id']);
            $user->structure_id = sanitize_input($_POST['structure_id']);
            $user->fonction = sanitize_input($_POST['fonction']);

            // Validation des données
            if (empty($user->nom) || empty($user->prenom) || empty($user->username) || empty($user->email) || empty($user->phone) || empty($user->password) || empty($user->role_id)) {
                echo json_encode(array('status' => 'info', 'message' => 'Veuillez remplir tous les champs.'));
                exit();
            }

            if ($user->create()) {
                $sendmailer->sendAccountCreate($user->email, $user->nom, $user->username, $user->password);
                $sendgreen->sendCreateAccount($user->phone, $user->username, $user->email);

                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Utilisateur ajouté avec succès.'));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de l\'ajout de l\'utilisateur.'));
            }
        } else {
            $user->id = $id;
            $user_data = $user->readById();

            $user->nom = sanitize_input($_POST['nom']);
            $user->prenom = sanitize_input($_POST['prenom']);
            $user->username = sanitize_input($_POST['username']);
            // $user->email = sanitize_input($_POST['email']);
            $user->phone = sanitize_input($_POST['phone']);
            // $user->password = sanitize_input($_POST['password']);
            $user->role_id = sanitize_input($_POST['role_id']);
            $user->structure_id = sanitize_input($_POST['structure_id']);
            $user->fonction = sanitize_input($_POST['fonction']);

            if ($user->update()) {
                $sendmailer->sendAccountUpdate($user_data['email'], $user_data['nom']);
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Utilisateur modifié avec succès.'));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'utilisateur.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $user->id = $id;
        $user_data = $user->readById();

        if ($user->delete()) {
            $sendmailer->sendAccountDelete($user_data['email'], $user_data['nom']);
            $sendgreen->sendDeleteAccount($user_data['phone'], $user_data['username']);

            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Utilisateur supprimé avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de l\'utilisateur.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $user->id = $id;
            if ($user->readById()) {
                $userData = $user->readById();
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Données de l\'utilisateur', 'data' => $userData));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune donnée trouvée pour l\'ID ' . $id));
            }
        } else {
            if ($user->read()) {
                $allData = $user->read();
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Données des utilisateurs', 'data' => $allData));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune donnée trouvée'));
            }
        }
        break;

    case 'PUT':
        $user->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);
        $user_data = $user->readById();

        if (isset($state) && $user->updateState($state)) {
            if ($state == 'actif') {
                $sendmailer->sendAccountActivate($user_data['email'], $user_data['nom']);
                $sendgreen->sendAccountActivate($user_data['phone'], $user_data['username']);
            } else {
                $sendmailer->sendAccountDeactivate($user_data['email'], $user_data['nom']);
                $sendgreen->sendAccountDeactivate($user_data['phone'], $user_data['username']);
            }
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Utilisateur modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'utilisateur.'));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}

// Close database connection
$db = null;
exit();
