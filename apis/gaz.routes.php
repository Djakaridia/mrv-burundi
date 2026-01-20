<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath .'config/database.php';
require_once $routePath .'models/Gaz.php';

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
$gaz = new Gaz($db);
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
            $gaz->id = $id;
            $result = $gaz->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la gaz', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune gaz trouvée avec cet identifiant.'));
            }
        } else {
            $result = $gaz->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des gaz', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune gaz trouvée.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $gaz->id = $id;
            $gaz->name = sanitize_input($_POST['name']);
            $gaz->couleur = sanitize_input($_POST['couleur']);
            $gaz->description = sanitize_input($_POST['description']);
            $gaz->add_by = sanitize_input($payload['user_id']);

            if (empty($gaz->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($gaz->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Gaz modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la gaz.'));
            }
        } else {
            $gaz->name = sanitize_input($_POST['name']);
            $gaz->couleur = sanitize_input($_POST['couleur']);
            $gaz->description = sanitize_input($_POST['description']);
            $gaz->add_by = sanitize_input($payload['user_id']);

            if (empty($gaz->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($gaz->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Gaz créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la gaz.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $gaz->id = $id;

        if ($gaz->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Gaz supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la gaz.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();