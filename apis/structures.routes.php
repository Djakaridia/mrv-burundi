<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/Structure.php';

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

$database = new Database();
$db = $database->getConnection();
$structure = new Structure($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uploadDirectory = $routePath . 'uploads/';

function sanitize_input($data)
{
    return htmlspecialchars(trim($data));
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id === null) {
            if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
                $structure->logo = uploadFile($_FILES['file'], $_POST['allow_files'], $uploadDirectory);
            } else {
                $structure->logo = sanitize_input($_POST['logo']);
            }
            
            $structure->code = sanitize_input($_POST['code']);
            $structure->sigle = sanitize_input($_POST['sigle']);
            $structure->email = sanitize_input($_POST['email']);
            $structure->phone = sanitize_input($_POST['phone']);
            $structure->address = sanitize_input($_POST['address']??"");
            $structure->description = sanitize_input($_POST['description']??"");
            $structure->type_id = sanitize_input($_POST['type_id']);
            $structure->add_by = sanitize_input($payload['user_id']);

            if (empty($structure->code) || empty($structure->sigle) || empty($structure->email) || empty($structure->phone) || empty($structure->type_id)) {
                die(json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs.')));
            }

            if ($structure->create()) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Acteur créé avec succès.'));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de l\'acteur.'));
            }
        } else {
            if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
                $structure->logo = uploadFile($_FILES['file'], $_POST['allow_files'], $uploadDirectory);
            } else {
                $structure->logo = sanitize_input($_POST['logo']);
            }

            $structure->id = $id;
            $structure->code = sanitize_input($_POST['code']);
            $structure->sigle = sanitize_input($_POST['sigle']);
            $structure->email = sanitize_input($_POST['email']);
            $structure->phone = sanitize_input($_POST['phone']);
            $structure->address = sanitize_input($_POST['address']??"");
            $structure->description = sanitize_input($_POST['description']??"");
            $structure->type_id = sanitize_input($_POST['type_id']);
            $structure->add_by = sanitize_input($payload['user_id']);

            if ($structure->update()) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Acteur modifié avec succès.'));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'acteur.'));
            }
        }
        break;

    case 'DELETE':
        $structure->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $structureData = $structure->readById();

        if ($structure->delete()) {
            deleteFile($structureData['logo']);
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Acteur supprimé avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de l\'acteur.'));
        }
        break;

    case 'GET':
        // Read
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $type_id = isset($_GET['type_id']) ? sanitize_input($_GET['type_id']) : null;

        // Read by ID
        if ($id) {
            $structure->id = $id;
            $data = $structure->readById();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Données de l\'acteur', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune donnée trouvée pour l\'ID ' . $id));
            }
        } elseif ($type_id) {
            $structure->type_id = $type_id;
            $data = $structure->readByType();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Données de l\'acteur', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune donnée trouvée pour l\'ID ' . $type_id));
            }
        } else {
            $data = $structure->read();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Liste des acteurs', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun acteur trouvée'));
            }
        }
        break;

    case 'PUT':
        $structure->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        if (isset($state) && $structure->updateState($state)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Acteur modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'acteur.'));
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