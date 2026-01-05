<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Departement.php';

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
$departement = new Departement($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if (!$id) {
            $departement->name = sanitize_input($_POST['name']);
            $departement->code = sanitize_input($_POST['code']);
            $departement->region = sanitize_input($_POST['parent']);
            $departement->add_by = sanitize_input($payload['user_id']);

            if (empty($departement->name) || empty($departement->code) || empty($departement->region)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                exit();
            }

            if ($departement->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Region créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la region.'));
            }
        } else {
            $departement->id = sanitize_input($_GET['id']);
            $departement->name = sanitize_input($_POST['name']);
            $departement->code = sanitize_input($_POST['code']);
            $departement->region = sanitize_input($_POST['parent']);
            $departement->add_by = sanitize_input($payload['user_id']);

            if ($departement->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Region modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la region.'));
            }
        }
        break;

    case 'DELETE':
        $departement->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($departement->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Region supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la region.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $departement->id = $id;
            $data = $departement->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails de la region', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Region non trouvée'));
            }
        } else {
            $data = $departement->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des regions', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune region trouvée'));
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
