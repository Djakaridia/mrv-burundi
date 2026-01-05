<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/SectionDash.php';

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
$section = new SectionDash($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $section->id = $id;
            $result = $section->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la section', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune section trouvée avec cet identifiant.'));
            }
        } else {
            $result = $section->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des sections', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune section trouvée.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $section->id = $id;
            $section->intitule = sanitize_input($_POST['intitule']);
            $section->position = sanitize_input($_POST['position']);
            $section->icone = sanitize_input($_POST['icone']);
            $section->couleur = sanitize_input($_POST['couleur']);
            $section->entity_type = sanitize_input($_POST['entity_type']);
            $section->entity_id = sanitize_input($_POST['entity_id']);
            $section->add_by = sanitize_input($payload['user_id']);

            if ($section->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Section modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la section.'));
            }
        } else {
            $section->intitule = sanitize_input($_POST['intitule']);
            $section->position = sanitize_input($_POST['position']);
            $section->icone = sanitize_input($_POST['icone']);
            $section->couleur = sanitize_input($_POST['couleur']);
            $section->entity_type = sanitize_input($_POST['entity_type']);
            $section->entity_id = sanitize_input($_POST['entity_id']);
            $section->add_by = sanitize_input($payload['user_id']);

            if (empty($section->intitule)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($section->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Section créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la section.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $section->id = $id;

        if ($section->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Section supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la section.'));
        }
        break;

    case 'PUT':
        $section->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        if (isset($state) && $section->updateState($state)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Section modifiée avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la section.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();