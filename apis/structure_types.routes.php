<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/StructureType.php';

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
$structureType = new StructureType($db);
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
            $structureType->id = $id;
            $result = $structureType->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données du sous secteur', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun sous secteur trouvé avec cet identifiant.'));
            }
        } else {
            $result = $structureType->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des sous secteurs', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun sous secteur trouvé.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $structureType->id = $id;
            $structureType->name = sanitize_input($_POST['name']);
            $structureType->description = sanitize_input($_POST['description']);
            $structureType->add_by = sanitize_input($payload['user_id']);
            
            if (empty($structureType->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($structureType->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Sous secteur modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du sous secteur.'));
            }
        } else {
            $structureType->name = sanitize_input($_POST['name']);
            $structureType->description = sanitize_input($_POST['description']);
            $structureType->add_by = sanitize_input($payload['user_id']);

            if ($structureType->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Type d\'acteur créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du sous secteur.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $structureType->id = $id;

        if ($structureType->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Sous secteur supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du sous secteur.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();