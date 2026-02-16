<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/Mesure.php';

configureCORS();
loadVarEnv();
header("Content-Type: application/json");

// Authentication 
try {
    $jwt = JWTUtils::getBearerToken();
    $payload = JWTUtils::validateJWT($jwt);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['status' => 'danger', 'message' => $e->getMessage()]);
    exit();
}

// Create a database connection
$database = new Database();
$db = $database->getConnection();
$mesure = new Mesure($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return $data === null ? null : trim((string)$data);
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $mesure->id = $id;
            $result = $mesure->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la mesure', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun mesure trouvé avec cet identifiant.'));
            }
        } else {
            $result = $mesure->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des mesures', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun mesure trouvé.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $mesure->id = $id;
            $mesure->code = sanitize_input($_POST['code']);
            $mesure->name = sanitize_input($_POST['name']);
            $mesure->status = sanitize_input($_POST['status'] ?? 'planifie');
            $mesure->secteur_id = (int)sanitize_input($_POST['secteur_id'] ?? 0) ?? null;
            $mesure->structure_id = (int)sanitize_input($_POST['structure_id'] ?? 0);
            $mesure->referentiel_id = (int)sanitize_input($_POST['referentiel_id'] ?? 0);
            $mesure->action_type = sanitize_input($_POST['action_type']);
            $mesure->instrument = sanitize_input($_POST['instrument']);
            $mesure->gaz = sanitize_input($_POST['gaz']);
            $mesure->unite = sanitize_input($_POST['unite']);
            $mesure->annee_debut = sanitize_input($_POST['annee_debut'] ?? null);
            $mesure->annee_fin = sanitize_input($_POST['annee_fin'] ?? null);
            $mesure->latitude = sanitize_input($_POST['latitude'] ?? null);
            $mesure->longitude = sanitize_input($_POST['longitude'] ?? null);
            $mesure->description = sanitize_input($_POST['description'] ?? '');
            $mesure->objectif = sanitize_input($_POST['objectif'] ?? '');
            $mesure->add_by = $payload['user_id'] ?? null;

            if (empty($mesure->code) || empty($mesure->name) || empty($mesure->structure_id) || empty($mesure->action_type)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs obligatoires !!!'));
                exit();
            }

            if ($mesure->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Mesure modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la mesure.'));
            }
        } else {
            $mesure->code = sanitize_input($_POST['code']);
            $mesure->name = sanitize_input($_POST['name']);
            $mesure->status = sanitize_input($_POST['status'] ?? 'planifie');
            $mesure->secteur_id = (int)sanitize_input($_POST['secteur_id'] ?? 0) ?? null;
            $mesure->structure_id = (int)sanitize_input($_POST['structure_id'] ?? 0);
            $mesure->referentiel_id = (int)sanitize_input($_POST['referentiel_id'] ?? 0);
            $mesure->action_type = sanitize_input($_POST['action_type']);
            $mesure->instrument = sanitize_input($_POST['instrument']);
            $mesure->gaz = sanitize_input($_POST['gaz']);
            $mesure->unite = sanitize_input($_POST['unite']);
            $mesure->annee_debut = sanitize_input($_POST['annee_debut'] ?? null);
            $mesure->annee_fin = sanitize_input($_POST['annee_fin'] ?? null);
            $mesure->latitude = sanitize_input($_POST['latitude'] ?? null);
            $mesure->longitude = sanitize_input($_POST['longitude'] ?? null);
            $mesure->description = sanitize_input($_POST['description'] ?? '');
            $mesure->objectif = sanitize_input($_POST['objectif'] ?? '');
            $mesure->add_by = $payload['user_id'] ?? null;


            if (empty($mesure->code) || empty($mesure->name) || empty($mesure->structure_id) || empty($mesure->action_type)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs obligatoires !!!'));
                exit();
            }

            if ($mesure->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Mesure créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la mesure.'));
            }
        }
        break;

    case 'PUT':
        $mesure->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        if (isset($state) && $mesure->updateState($state)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Mesure modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du Mesure.'));
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $mesure->id = $id;

        if ($mesure->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Mesure supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la mesure.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();
