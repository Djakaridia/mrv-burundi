<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/GroupeProjets.php';

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

$database = new Database();
$db = $database->getConnection();
$groupeProjets = new GroupeProjets($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

switch ($requestMethod) {
    case 'POST':
        $groupe_id = isset($_GET['groupe_id']) ? sanitize_input($_GET['groupe_id']) : null;
        if ($groupe_id === null) {
            // Create
            $groupeProjets->groupe_id = sanitize_input($_POST['groupe_id']);
            $groupeProjets->projet_id = sanitize_input($_POST['projet_id']);

            if (empty($groupeProjets->groupe_id) || empty($groupeProjets->projet_id)) {
                die(json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs.')));
            }

            if ($groupeProjets->create()) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Association groupe-projet créée avec succès.'));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de l\'association.'));
            }
        } else {
            // Update
            $groupeProjets->groupe_id = sanitize_input($groupe_id);
            $groupeProjets->projet_id = sanitize_input($_POST['projet_id']);

            if ($groupeProjets->update()) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Association groupe-projet modifiée avec succès.'));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'association.'));
            }
        }
        break;

    case 'DELETE':
        $groupeProjets->groupe_id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($groupeProjets->delete()) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Association groupe-projet supprimée avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de l\'association.'));
        }
        break;

    case 'GET':
        $groupe_id = isset($_GET['groupe_id']) ? sanitize_input($_GET['groupe_id']) : null;
        $projet_id = isset($_GET['projet_id']) ? sanitize_input($_GET['projet_id']) : null;

        if ($groupe_id) {
            $groupeProjets->groupe_id = $groupe_id;
            $data = $groupeProjets->readByGroupe();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Projets du groupe', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun projet trouvé pour ce groupe'));
            }
        } elseif ($projet_id) {
            $groupeProjets->projet_id = $projet_id;
            $data = $groupeProjets->readByProjet();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Groupes du projet', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun groupe trouvé pour ce projet'));
            }
        } else {
            $data = $groupeProjets->read();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Liste des associations groupe-projet', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune association trouvée'));
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}

// Close database connection
$db = null;
exit();