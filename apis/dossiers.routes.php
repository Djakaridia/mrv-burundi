<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath .'config/database.php';
require_once $routePath .'models/Dossier.php';

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
$dossier = new Dossier($db);
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
            $dossier->id = $id;
            $result = $dossier->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la dossier', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune dossier trouvée avec cet identifiant.'));
            }
        } else {
            $result = $dossier->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des dossiers', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune dossier trouvée.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $dossier->id = $id;
            $dossier->name = sanitize_input($_POST['name']);
            $dossier->description = sanitize_input($_POST['description']);
            $dossier->type = sanitize_input('autres');
            $dossier->parent_id = sanitize_input($_POST['parent_id'] ?? 0);
            $dossier->add_by = sanitize_input($payload['user_id']);

            if (empty($dossier->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($dossier->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Dossier modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du dossier.'));
            }
        } else {
            $dossier->name = sanitize_input($_POST['name']);
            $dossier->description = sanitize_input($_POST['description']);
            $dossier->type = sanitize_input('autres');
            $dossier->parent_id = sanitize_input($_POST['parent_id']);
            $dossier->add_by = sanitize_input($payload['user_id']);

            if (empty($dossier->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($dossier->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Dossier créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du dossier.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $dossier->id = $id;

        if ($dossier->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Dossier supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la priorité.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();