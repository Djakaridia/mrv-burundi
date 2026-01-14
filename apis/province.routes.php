<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Province.php';
require_once $routePath . 'config/functions.php';

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
$province = new Province($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uploadDirectory = $routePath . 'uploads/couches/';

function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if (!$id) {
            if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
                $province->couches = uploadFile($_FILES['file'], $_POST['allow_files'], $uploadDirectory);
            } else {
                $province->couches = sanitize_input($_POST['couches']);
            }

            $province->name = sanitize_input($_POST['name']);
            $province->code = sanitize_input($_POST['code']);
            $province->sigle = sanitize_input($_POST['sigle']);
            $province->couleur = sanitize_input($_POST['couleur']);
            $province->add_by = sanitize_input($payload['user_id']);

            if (empty($province->name) || empty($province->code)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                exit();
            }

            if ($province->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Province créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la province.'));
            }
        } else {
            if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
                $province->couches = uploadFile($_FILES['file'], $_POST['allow_files'], $uploadDirectory);
            } else {
                $province->couches = sanitize_input($_POST['couches']);
            }

            $province->id = sanitize_input($_GET['id']);
            $province->name = sanitize_input($_POST['name']);
            $province->code = sanitize_input($_POST['code']);
            $province->sigle = sanitize_input($_POST['sigle']);
            $province->couleur = sanitize_input($_POST['couleur']);
            $province->add_by = sanitize_input($payload['user_id']);

            if ($province->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Province modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la province.'));
            }
        }
        break;

    case 'DELETE':
        $province->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $provinceData = $province->readById();

        if ($province->delete()) {
            deleteFile($provinceData['couches']);
            echo json_encode(array('status' => 'success', 'message' => 'Province supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la province.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $province->id = $id;
            $data = $province->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails de la province', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Province non trouvée'));
            }
        } else {
            $data = $province->read();
            if ($data) {    
                echo json_encode(array('status' => 'success', 'message' => 'Liste des provinces', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune province trouvée'));
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
