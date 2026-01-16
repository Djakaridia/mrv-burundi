<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Role.php';

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
$role = new Role($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return trim($data);
}


switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id === null) {
            $role->name = sanitize_input($_POST['name']);
            $role->niveau = sanitize_input($_POST['niveau']);
            $role->description = sanitize_input($_POST['description']);

            if (empty($role->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($role->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Role ajouté avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de l\'ajout du role.'));
            }
        } else {
            $role->id = $id;
            $role->name = sanitize_input($_POST['name']);
            $role->niveau = sanitize_input($_POST['niveau']);
            $role->description = sanitize_input($_POST['description']);

            $pageEdit = $_POST['page_edit'] ?? [];
            $pageDelete = $_POST['page_delete'] ?? [];
            $pageInterdite = $_POST['page_interdite'] ?? [];

            $role->page_edit = implode("|", $pageEdit);
            $role->page_delete = implode("|", $pageDelete);
            $role->page_interdite = implode("|", $pageInterdite);

            if ($role->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Role modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du role.'));
            }
        }

        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $role->id = $id;

        if ($role->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Role supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du role.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $role->id = $id;
            $roleData = $role->readById();
            if ($roleData) {
                echo json_encode(array('status' => 'success', 'message' => 'Données du role', 'data' => $roleData));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune donnée trouvée pour l\'ID ' . $id));
            }
        } else {
            $allData = $role->read();
            if ($allData) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des roles', 'data' => $allData));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune donnée trouvée'));
            }
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();
