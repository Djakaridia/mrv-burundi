<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Colline.php';

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
$colline = new Colline($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return htmlspecialchars(trim($data));
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if (!$id) {
            // Create
            $colline->code = sanitize_input($_POST['code']);
            $colline->name = sanitize_input($_POST['name']);
            $colline->commune = sanitize_input($_POST['parent']);
            $colline->longitude = sanitize_input($_POST['longitude'] ?? null);
            $colline->latitude = sanitize_input($_POST['latitude'] ?? null);
            $colline->hommes = sanitize_input($_POST['hommes'] ?? 0);
            $colline->femmes = sanitize_input($_POST['femmes'] ?? 0);
            $colline->jeunes = sanitize_input($_POST['jeunes'] ?? 0);
            $colline->adultes = sanitize_input($_POST['adultes'] ?? 0);
            $colline->add_by = sanitize_input($payload['user_id']);

            if (empty($colline->name) || empty($colline->code) || empty($colline->commune)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                exit();
            }

            if ($colline->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Colline créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du colline.'));
            }
        } else {
            // Update
            $colline->id = sanitize_input($_GET['id']);
            $colline->code = sanitize_input($_POST['code']);
            $colline->name = sanitize_input($_POST['name']);
            $colline->commune = sanitize_input($_POST['parent']);
            $colline->longitude = sanitize_input($_POST['longitude'] ?? null);
            $colline->latitude = sanitize_input($_POST['latitude'] ?? null);
            $colline->hommes = sanitize_input($_POST['hommes'] ?? 0);
            $colline->femmes = sanitize_input($_POST['femmes'] ?? 0);
            $colline->jeunes = sanitize_input($_POST['jeunes'] ?? 0);
            $colline->adultes = sanitize_input($_POST['adultes'] ?? 0);
            $colline->add_by = sanitize_input($payload['user_id']);

            if ($colline->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Colline modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du colline.'));
            }
        }
        break;

    case 'DELETE':
        $colline->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($colline->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Colline supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du colline.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $colline->id = $id;
            $data = $colline->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails du colline', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Colline non trouvé'));
            }
        } else {
            $data = $colline->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des collines', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun colline trouvée'));
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
