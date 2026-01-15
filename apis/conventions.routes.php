<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Convention.php';

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
$convention = new Convention($db);
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
            $convention->id = $id;
            $result = $convention->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la convention', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune convention trouvée avec cet identifiant.'));
            }
        } else {
            $result = $convention->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des conventions', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune convention trouvée.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $convention->id = $id;
            $convention->code = sanitize_input($_POST['code']);
            $convention->name = sanitize_input($_POST['name']);
            $convention->montant = sanitize_input($_POST['montant']);
            $convention->date_accord = sanitize_input($_POST['date_accord']);
            $convention->structure_id = sanitize_input($_POST['structure_id']);
            $convention->projet_id = sanitize_input($_POST['projet_id']);
            $convention->add_by = sanitize_input($payload['user_id']);

            if ($convention->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Convention modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la convention.'));
            }
        } else {
            $convention->name = sanitize_input($_POST['name']);
            $convention->code = sanitize_input($_POST['code']);
            $convention->montant = sanitize_input($_POST['montant']);
            $convention->date_accord = sanitize_input($_POST['date_accord']);
            $convention->structure_id = sanitize_input($_POST['structure_id']);
            $convention->projet_id = sanitize_input($_POST['projet_id']);
            $convention->add_by = sanitize_input($payload['user_id']);

            if (empty($convention->name) || empty($convention->montant) || empty($convention->structure_id)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($convention->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Convention créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la convention.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $convention->id = $id;

        if ($convention->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Convention supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la convention.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();