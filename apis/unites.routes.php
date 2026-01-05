<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath .'config/database.php';
require_once $routePath .'models/Unite.php';

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
$unite = new Unite($db);
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
            $unite->id = $id;
            $result = $unite->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de l\'unité', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune unité trouvée.'));
            }
        } else {
            $result = $unite->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des unités', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune unité trouvée.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $unite->id = $id;
            $unite->name = sanitize_input($_POST['name']);
            $unite->description = sanitize_input($_POST['description']);
            $unite->add_by = sanitize_input($payload['user_id']);

            if (empty($unite->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($unite->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Unité modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'unité.'));
            }
        } else {
            $unite->name = sanitize_input($_POST['name']);
            $unite->description = sanitize_input($_POST['description']);
            $unite->add_by = sanitize_input($payload['user_id']);

            if (empty($unite->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($unite->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Unité créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de l\'unité.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $unite->id = $id;

        if ($unite->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Unité supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de l\'unité.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();