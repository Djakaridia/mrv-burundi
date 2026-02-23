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
$action = new ActionPrioritaire($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $action->id = $id;
            $result = $action->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données du action', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun action trouvé avec cet identifiant.'));
            }
        } else {
            $result = $action->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des actions', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun action trouvé.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $action->id = $id;
            $action->code = sanitize_input($_POST['code']);
            $action->name = sanitize_input($_POST['name']);
            $action->description = sanitize_input($_POST['description']);
            $action->objectif_wem = sanitize_input($_POST['objectif_wem'] ?? "N/A");
            $action->objectif_wam = sanitize_input($_POST['objectif_wam'] ?? "N/A");
            $action->action_type = sanitize_input($_POST['action_type'] ?? "N/A");
            $action->secteur_id = (int)sanitize_input($_POST['secteur_id'] ?? 0);
            $action->sous_secteur_id = (int)sanitize_input($_POST['sous_secteur_id'] ?? 0);
            $action->add_by = sanitize_input($payload['user_id']);

            if ($action->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Action modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du action.'));
            }
        } else {
            $action->code = sanitize_input($_POST['code']);
            $action->name = sanitize_input($_POST['name']);
            $action->description = sanitize_input($_POST['description']);
            $action->objectif_wem = sanitize_input($_POST['objectif_wem'] ?? "N/A");
            $action->objectif_wam = sanitize_input($_POST['objectif_wam'] ?? "N/A");
            $action->action_type = sanitize_input($_POST['action_type'] ?? "N/A");
            $action->secteur_id = (int)sanitize_input($_POST['secteur_id'] ?? 0);
            $action->sous_secteur_id = (int)sanitize_input($_POST['sous_secteur_id'] ?? 0);
            $action->add_by = sanitize_input($payload['user_id']);

            if (empty($action->name) || empty($action->code)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($action->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Action créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du action.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $action->id = $id;

        if ($action->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Action supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du action.'));
        }
        break;

    case 'PUT':
        $action->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        if (isset($state) && $action->updateState($state)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Action modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du action.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();
