<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Secteur.php';

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
$secteur = new Secteur($db);
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
            $secteur->id = $id;
            $result = $secteur->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données du secteur', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun secteur trouvé avec cet identifiant.'));
            }
        } else {
            $result = $secteur->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des secteurs', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun secteur trouvé.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $secteur->id = $id;
            $secteur->name = sanitize_input($_POST['name']);
            $secteur->code = sanitize_input($_POST['code']);
            $secteur->structure_id = sanitize_input($_POST['structure_id']);
            $secteur->nature = sanitize_input($_POST['nature']??"N/A");
            $secteur->source = sanitize_input($_POST['source']??"N/A");
            $secteur->parent = sanitize_input($_POST['parent'] ?? 0);
            $secteur->description = sanitize_input($_POST['description']);
            $secteur->add_by = sanitize_input($payload['user_id']);

            if ($secteur->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Secteur modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du secteur.'));
            }
        } else {
            $secteur->name = sanitize_input($_POST['name']);
            $secteur->code = sanitize_input($_POST['code']);
            $secteur->structure_id = sanitize_input($_POST['structure_id']);
            $secteur->nature = sanitize_input($_POST['nature']??"N/A");
            $secteur->source = sanitize_input($_POST['source']??"N/A");
            $secteur->parent = sanitize_input($_POST['parent'] ?? 0);
            $secteur->description = sanitize_input($_POST['description']);
            $secteur->add_by = sanitize_input($payload['user_id']);

            if (empty($secteur->name) || empty($secteur->code)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($secteur->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Secteur créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du secteur.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $secteur->id = $id;

        if ($secteur->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Secteur supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du secteur.'));
        }
        break;

    case 'PUT':
        $secteur->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        if (isset($state) && $secteur->updateState($state)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Secteur modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du secteur.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();