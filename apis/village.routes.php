<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Village.php';

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
$village = new Village($db);
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
            $village->code = sanitize_input($_POST['code']);
            $village->name = sanitize_input($_POST['name']);
            $village->commune = sanitize_input($_POST['parent']);
            $village->longitude = sanitize_input($_POST['longitude'] ?? null);
            $village->latitude = sanitize_input($_POST['latitude'] ?? null);
            $village->hommes = sanitize_input($_POST['hommes'] ?? 0);
            $village->femmes = sanitize_input($_POST['femmes'] ?? 0);
            $village->jeunes = sanitize_input($_POST['jeunes'] ?? 0);
            $village->adultes = sanitize_input($_POST['adultes'] ?? 0);
            $village->add_by = sanitize_input($payload['user_id']);

            if (empty($village->name) || empty($village->code) || empty($village->commune)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                exit();
            }

            if ($village->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Village créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du village.'));
            }
        } else {
            // Update
            $village->id = sanitize_input($_GET['id']);
            $village->code = sanitize_input($_POST['code']);
            $village->name = sanitize_input($_POST['name']);
            $village->commune = sanitize_input($_POST['parent']);
            $village->longitude = sanitize_input($_POST['longitude'] ?? null);
            $village->latitude = sanitize_input($_POST['latitude'] ?? null);
            $village->hommes = sanitize_input($_POST['hommes'] ?? 0);
            $village->femmes = sanitize_input($_POST['femmes'] ?? 0);
            $village->jeunes = sanitize_input($_POST['jeunes'] ?? 0);
            $village->adultes = sanitize_input($_POST['adultes'] ?? 0);
            $village->add_by = sanitize_input($payload['user_id']);

            if ($village->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Village modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du village.'));
            }
        }
        break;

    case 'DELETE':
        $village->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($village->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Village supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du village.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $village->id = $id;
            $data = $village->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails du village', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Village non trouvé'));
            }
        } else {
            $data = $village->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des villages', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun village trouvée'));
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
