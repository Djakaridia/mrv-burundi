<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Commune.php';

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
$commune = new Commune($db);
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
            $commune->name = sanitize_input($_POST['name']);
            $commune->code = sanitize_input($_POST['code']);
            $commune->province = sanitize_input($_POST['parent']);
            $commune->add_by = sanitize_input($payload['user_id']);

            if (empty($commune->name) || empty($commune->code) || empty($commune->province)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                exit();
            }

            if ($commune->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Commune créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la commune.'));
            }
        } else {
            // Update
            $commune->id = sanitize_input($_GET['id']);
            $commune->name = sanitize_input($_POST['name']);
            $commune->code = sanitize_input($_POST['code']);
            $commune->province = sanitize_input($_POST['parent']);
            $commune->add_by = sanitize_input($payload['user_id']);

            if ($commune->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Commune modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la commune.'));
            }
        }
        break;

    case 'DELETE':
        $commune->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($commune->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Commune supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la commune.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $commune->id = $id;
            $data = $commune->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails de la commune', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Commune non trouvée'));
            }
        } else {
            $data = $commune->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des communes', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune commune trouvée'));
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
