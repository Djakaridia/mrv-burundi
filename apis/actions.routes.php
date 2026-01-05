<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Action.php';

configureCORS();
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
$action = new Action($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if (!$id) {
            // Create
            $action->name = sanitize_input($_POST['name']);
            $action->code = sanitize_input($_POST['code']);
            $action->description = sanitize_input($_POST['description']);
            $action->objectif = sanitize_input($_POST['objectif']);
            $action->secteur_id = sanitize_input($_POST['secteur_id']);
            $action->add_by = sanitize_input($payload['user_id']);

            if (empty($action->name) || empty($action->code) || empty($action->secteur_id)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                exit();
            }

            if ($action->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Action créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de l\'action.'));
            }
        } else {
            // Update
            $action->id = sanitize_input($_GET['id']);
            $action->name = sanitize_input($_POST['name']);
            $action->code = sanitize_input($_POST['code']);
            $action->description = sanitize_input($_POST['description']);
            $action->objectif = sanitize_input($_POST['objectif']);
            $action->secteur_id = sanitize_input($_POST['secteur_id']);
            $action->add_by = sanitize_input($payload['user_id']);

            if ($action->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Action modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'action.'));
            }
        }
        break;

    case 'DELETE':
        $action->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($action->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Action supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de l\'action.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $action->id = $id;
            $data = $action->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails de l\'action', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Action non trouvée'));
            }
        } else {
            $data = $action->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des actions', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune action trouvée'));
            }
        }
        break;

    default:
        echo json_encode(array("message" => "Method not allowed."));
        break;
}

// Close database connection
$db = null;
exit();
