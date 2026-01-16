<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/ZoneType.php';

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
$zone_type = new ZoneType($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if (!$id) {
            // Create
            $zone_type->name = sanitize_input($_POST['name']);
            $zone_type->description = sanitize_input($_POST['description']);
            $zone_type->add_by = sanitize_input($payload['user_id']);

            if (empty($zone_type->name)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                exit();
            }

            if ($zone_type->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Type de zone créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du type de zone.'));
            }
        } else {
            // Update
            $zone_type->id = sanitize_input($_GET['id']);
            $zone_type->name = sanitize_input($_POST['name']);
            $zone_type->description = sanitize_input($_POST['description']);
            $zone_type->add_by = sanitize_input($payload['user_id']);

            if ($zone_type->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Type de zone modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du type de zone.'));
            }
        }
        break;

    case 'DELETE':
        $zone_type->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($zone_type->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Type de zone supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du type de zone.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $zone_type->id = $id;
            $data = $zone_type->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails du type de zone', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Type de zone non trouvé'));
            }
        } else {
            $data = $zone_type->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des types de zones', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun type de zone trouvé'));
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
