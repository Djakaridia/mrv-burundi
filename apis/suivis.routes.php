<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Suivi.php';

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
$suivi = new Suivi($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $suivi->id = $id;
            $suivi->cmr_id = sanitize_input($_POST['cmr_id']);
            $suivi->projet_id = sanitize_input($_POST['projet_id']);
            $suivi->scenario = sanitize_input($_POST['scenario']);
            $suivi->echelle = sanitize_input($_POST['echelle'] ?? '');
            $suivi->classe = sanitize_input($_POST['classe'] ?? '');
            $suivi->annee = sanitize_input($_POST['annee']);
            $suivi->date_suivie = sanitize_input($_POST['date_suivie']);
            $suivi->valeur = sanitize_input($_POST['valeur']);
            $suivi->observation = sanitize_input($_POST['observation']);
            $suivi->add_by = sanitize_input($payload['user_id']);

            if (empty($suivi->cmr_id) || empty($suivi->projet_id) || empty($suivi->annee) || empty($suivi->date_suivie) || empty($suivi->valeur)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($suivi->update()) {
                echo json_encode(array('status' => 'success', 'message' => "Suivi modifiée avec succès."));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Erreur lors de la modification de la suivi.'));
            }
        } else {
            $suivi->cmr_id = sanitize_input($_POST['cmr_id']);
            $suivi->projet_id = sanitize_input($_POST['projet_id']);
            $suivi->scenario = sanitize_input($_POST['scenario']);
            $suivi->echelle = sanitize_input($_POST['echelle'] ?? '');
            $suivi->classe = sanitize_input($_POST['classe'] ?? '');
            $suivi->annee = sanitize_input($_POST['annee']);
            $suivi->date_suivie = sanitize_input($_POST['date_suivie']);
            $suivi->valeur = sanitize_input($_POST['valeur']);
            $suivi->observation = sanitize_input($_POST['observation']);
            $suivi->add_by = sanitize_input($payload['user_id']);

            if (empty($suivi->cmr_id) || empty($suivi->projet_id) || empty($suivi->annee) || empty($suivi->date_suivie) || empty($suivi->valeur)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($suivi->create()) {
                echo json_encode(array('status' => 'success', 'message' => "Suivi créé avec succès."));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Erreur lors de la création de la suivi.'));
            }
        }
        break;

    case 'DELETE':
        $suivi->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($suivi->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Suivi supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la valeur suivi.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $crm_id = isset($_GET['crm_id']) ? sanitize_input($_GET['crm_id']) : null;

        if ($id) {
            $suivi->id = $id;
            $data = $suivi->readById();

            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails de la valeur suivi', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Suivi non trouvée'));
            }
        } else if ($crm_id) {
            $suivi->cmr_id = $crm_id;
            $data = $suivi->readByCMR();

            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails de la valeur suivi', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Suivi non trouvée'));
            }
        } else {
            $data = $suivi->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des suivis', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune suivi trouvée'));
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
