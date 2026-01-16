<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/GroupeUsers.php';
require_once $routePath . 'models/GroupeTravail.php';
require_once $routePath . 'models/User.php';
require_once $routePath . 'services/groupe.green.php';

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

$database = new Database();
$db = $database->getConnection();
$groupeUsers = new GroupeUsers($db);
$sendgreen = new GroupeGreen($_ENV['API_GREEN_INSTANCE'], $_ENV['API_GREEN_TOKEN'], $_ENV['API_GREEN_IND']);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data) {
    return trim($data);
}

// function getUserData($groupe_id) {
//     global $db;
//     $groupeTravail = new GroupeTravail($db);
//     $groupeTravail->id = $groupe_id;
//     $groupeTravail_data = $groupeTravail->readById();

//     $user = new User($db);
//     $user->id = $groupeTravail_data['user_id'];
//     $user_data = $user->readById();

//     return array(
//         'id' => $groupe_id,
//         'groupe_id' => $groupeUsers_data['groupe_id'],
//         'user_id' => $groupeUsers_data['user_id'],
//         'groupe_name' => $groupeTravail_data['name'],
//         'user_name' => $user_data['username'],
//         'user_phone' => $user_data['phone'],
//     );
// }

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id === null) {
            // Create
            $groupeUsers->groupe_id = sanitize_input($_POST['groupe_id']);
            $groupeUsers->user_id = sanitize_input($_POST['user_id']);

            if (empty($groupeUsers->groupe_id) || empty($groupeUsers->user_id)) {
                die(json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs.')));
            }

            if ($groupeUsers->create()) {
                $groupeTravail = new GroupeTravail($db);
                $groupeTravail->id = $groupeUsers->groupe_id;
                $groupeTravail_data = $groupeTravail->readById();

                $user = new User($db);
                $user->id = $groupeUsers->user_id;
                $user_data = $user->readById();

                $sendgreen->sendAddMember($user_data['phone'], $user_data['username'], $groupeTravail_data['name']);

                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Association groupe-utilisateur créée avec succès.'));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de l\'association.'));
            }
        } else {
            // Update
            $groupeUsers->id = $id;
            $groupeUsers->groupe_id = sanitize_input($_POST['groupe_id']);
            $groupeUsers->user_id = sanitize_input($_POST['user_id']);

            if ($groupeUsers->update()) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Association groupe-utilisateur modifiée avec succès.'));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'association.'));
            }
        }
        break;

    case 'DELETE':
        $groupeUsers->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $groupeUsers_data = $groupeUsers->readById();

        $groupeTravail = new GroupeTravail($db);
        $groupeTravail->id = $groupeUsers_data['groupe_id'];
        $groupeTravail_data = $groupeTravail->readById();

        $user = new User($db);
        $user->id = $groupeUsers_data['user_id'];
        $user_data = $user->readById();

        if ($groupeUsers->delete()) {
            $sendgreen->sendRemoveMember($user_data['phone'], $user_data['username'], $groupeTravail_data['name']);

            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Association groupe-utilisateur supprimée avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de l\'association.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $groupe_id = isset($_GET['groupe_id']) ? sanitize_input($_GET['groupe_id']) : null;
        $user_id = isset($_GET['user_id']) ? sanitize_input($_GET['user_id']) : null;

        if ($id) {
            $groupeUsers->id = $id;
            $data = $groupeUsers->readById();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Données de l\'association', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune donnée trouvée pour l\'ID ' . $id));
            }
        } elseif ($groupe_id) {
            $groupeUsers->groupe_id = $groupe_id;
            $data = $groupeUsers->readByGroupeId();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Utilisateurs du groupe', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun utilisateur trouvé pour ce groupe'));
            }
        } elseif ($user_id) {
            $groupeUsers->user_id = $user_id;
            $data = $groupeUsers->readByUserId();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Groupes de l\'utilisateur', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun groupe trouvé pour cet utilisateur'));
            }
        } else {
            $data = $groupeUsers->read();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Liste des associations groupe-utilisateur', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune association trouvée'));
            }
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