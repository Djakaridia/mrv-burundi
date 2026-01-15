<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Programme.php';

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
$programme = new Programme($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return htmlspecialchars(trim($data));
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $programme->id = $id;
            $result = $programme->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données du programme', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun programme trouvé avec cet identifiant.'));
            }
        } else {
            $result = $programme->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des programmes', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun programme trouvé.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $programme->id = $id;
            $programme->name = sanitize_input($_POST['name']);
            $programme->sigle = sanitize_input($_POST['sigle']);
            $programme->code = sanitize_input($_POST['code']);
            $programme->description = sanitize_input($_POST['description']);
            $programme->status = sanitize_input($_POST['status']);
            $programme->add_by = sanitize_input($payload['user_id']);

            if (empty($programme->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($programme->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Programme modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du programme.'));
            }
        } else {
            $programme->name = sanitize_input($_POST['name']);
            $programme->sigle = sanitize_input($_POST['sigle']);
            $programme->code = sanitize_input($_POST['code']);
            $programme->description = sanitize_input($_POST['description']);
            $programme->status = sanitize_input($_POST['status']);
            $programme->add_by = sanitize_input($payload['user_id']);

            if (empty($programme->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($programme->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Programme créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du programme.'));
            }
        }
        break;

    case 'PUT':
        $programme->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        if (isset($state) && $programme->updateState($state)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Programme modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du Programme.'));
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $programme->id = $id;

        if ($programme->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Programme supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du programme.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();