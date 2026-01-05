<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath .'config/database.php';
require_once $routePath .'models/Priorite.php';

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
$priorite = new Priorite($db);
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
            $priorite->id = $id;
            $result = $priorite->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la priorité', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune priorité trouvée avec cet identifiant.'));
            }
        } else {
            $result = $priorite->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des priorités', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune priorité trouvée.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $priorite->id = $id;
            $priorite->name = sanitize_input($_POST['name']);
            $priorite->couleur = sanitize_input($_POST['couleur']);
            $priorite->description = sanitize_input($_POST['description']);
            $priorite->add_by = sanitize_input($payload['user_id']);

            if (empty($priorite->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($priorite->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Priorité modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la priorité.'));
            }
        } else {
            $priorite->name = sanitize_input($_POST['name']);
            $priorite->couleur = sanitize_input($_POST['couleur']);
            $priorite->description = sanitize_input($_POST['description']);
            $priorite->add_by = sanitize_input($payload['user_id']);

            if (empty($priorite->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($priorite->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Priorité créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la priorité.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $priorite->id = $id;

        if ($priorite->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Priorité supprimée avec succès.'));
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