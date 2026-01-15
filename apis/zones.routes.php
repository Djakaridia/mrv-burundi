<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/Zone.php';


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
$zone = new Zone($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uploadDirectory = $routePath . 'uploads/couches/';

function sanitize_input($data)
{
    return htmlspecialchars(trim($data));
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if (!$id) {
            if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
                $zone->couches = uploadFile($_FILES['file'], $_POST['allow_files'], $uploadDirectory);
            } else {
                $zone->couches = sanitize_input($_POST['couches']);
            }

            $zone->name = sanitize_input($_POST['name']);
            $zone->code = sanitize_input($_POST['code']);
            $zone->superficie = sanitize_input($_POST['superficie']);
            $zone->couleur = sanitize_input($_POST['couleur']);
            $zone->afficher = sanitize_input($_POST['afficher']);
            $zone->description = sanitize_input($_POST['description']);
            $zone->type_id = sanitize_input($_POST['type_id']);
            $zone->add_by = sanitize_input($payload['user_id']);

            if (empty($zone->name) || empty($zone->code) || empty($zone->type_id)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                exit();
            }

            if ($zone->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Zone créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la zone.'));
            }
        } else {
            if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
                $zone->couches = uploadFile($_FILES['file'], $_POST['allow_files'], $uploadDirectory);
            } else {
                $zone->couches = sanitize_input($_POST['couches']);
            }

            $zone->id = sanitize_input($_GET['id']);
            $zone->name = sanitize_input($_POST['name']);
            $zone->code = sanitize_input($_POST['code']);
            $zone->superficie = sanitize_input($_POST['superficie']);
            $zone->couleur = sanitize_input($_POST['couleur']);
            $zone->afficher = sanitize_input($_POST['afficher']);
            $zone->description = sanitize_input($_POST['description']);
            $zone->type_id = sanitize_input($_POST['type_id']);
            $zone->add_by = sanitize_input($payload['user_id']);

            if ($zone->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Zone modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la zone.'));
            }
        }
        break;

    case 'DELETE':
        $zone->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $zoneData = $zone->readById();

        if ($zone->delete()) {
            deleteFile($zoneData['couches']);
            echo json_encode(array('status' => 'success', 'message' => 'Zone supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la zone.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $type_id = isset($_GET['type_id']) ? sanitize_input($_GET['type_id']) : null;

        if ($id) {
            $zone->id = $id;
            $data = $zone->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails de la zone', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Zone non trouvée'));
            }
        } else {
            if ($type_id) {
                $zone->type_id = $type_id;
                $data = $zone->readByTypeId();
                if ($data) {
                    echo json_encode(array('status' => 'success', 'message' => 'Liste des zones', 'data' => $data));
                } else {
                    echo json_encode(array('status' => 'warning', 'message' => 'Aucune zone trouvée'));
                }
            } else {
                $data = $zone->read();
                if ($data) {
                    echo json_encode(array('status' => 'success', 'message' => 'Liste des zones', 'data' => $data));
                } else {
                    echo json_encode(array('status' => 'warning', 'message' => 'Aucune zone trouvée'));
                }
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
