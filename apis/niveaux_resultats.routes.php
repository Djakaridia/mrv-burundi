<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/NiveauResultat.php';

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
$niveauResult = new NiveauResultat($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id == null) {
            // Create
            $niveauResult->code = sanitize_input($_POST['code']);
            $niveauResult->name = sanitize_input($_POST['name']);
            $niveauResult->niveau = sanitize_input($_POST['niveau']);
            $niveauResult->programme = sanitize_input($_POST['programme']);
            $niveauResult->add_by = sanitize_input($payload['user_id']);

            if (empty($niveauResult->code) || empty($niveauResult->name)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                exit();
            }

            if ($niveauResult->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Niveau résultat créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du niveau résultat.'));
            }
        } else {
            // Update
            $niveauResult->id = sanitize_input($_GET['id']);
            $niveauResult->code = sanitize_input($_POST['code']);
            $niveauResult->name = sanitize_input($_POST['name']);
            $niveauResult->niveau = sanitize_input($_POST['niveau']);
            $niveauResult->programme = sanitize_input($_POST['programme']);
            $niveauResult->add_by = sanitize_input($payload['user_id']);

            if ($niveauResult->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Niveau résultat modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du niveau résultat.'));
            }
        }
        break;

    case 'DELETE':
        $niveauResult->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($niveauResult->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Niveau résultat supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du niveau résultat.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $niveauResult->id = $id;
            $data = $niveauResult->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails du niveau résultat', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Niveau résultat non trouvé'));
            }
        } else {
            $data = $niveauResult->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des niveaux de résultats', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun niveau de résultat trouvé'));
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