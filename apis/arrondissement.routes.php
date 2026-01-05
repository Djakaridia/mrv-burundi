<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Arrondissement.php';

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
$arrondissement = new Arrondissement($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if (!$id) {
            $arrondissement->name = sanitize_input($_POST['name']);
            $arrondissement->code = sanitize_input($_POST['code']);
            $arrondissement->departement = sanitize_input($_POST['parent']);
            $arrondissement->add_by = sanitize_input($payload['user_id']);

            if (empty($arrondissement->name) || empty($arrondissement->code) || empty($arrondissement->departement)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                exit();
            }

            if ($arrondissement->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Arrondissement créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de l\'arrondissement.'));
            }
        } else {
            $arrondissement->id = sanitize_input($_GET['id']);
            $arrondissement->name = sanitize_input($_POST['name']);
            $arrondissement->code = sanitize_input($_POST['code']);
            $arrondissement->departement = sanitize_input($_POST['parent']);
            $arrondissement->add_by = sanitize_input($payload['user_id']);

            if ($arrondissement->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Arrondissement modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'arrondissement.'));
            }
        }
        break;

    case 'DELETE':
        $arrondissement->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($arrondissement->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Arrondissement supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de l\'arrondissement.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $arrondissement->id = $id;
            $data = $arrondissement->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails de l\'arrondissement', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Arrondissement non trouvé'));
            }
        } else {
            $data = $arrondissement->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des arrondissements', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun arrondissement trouvé'));
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
