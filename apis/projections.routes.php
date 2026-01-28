<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/Projection.php';

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
$projection = new Projection($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return $data === null ? null : trim((string)$data);
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $secteur_id = isset($_GET['secteur']) ? sanitize_input($_GET['secteur']) : null;

        if ($id) {
            $projection->id = $id;
            $result = $projection->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la projection', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune projection trouvée avec cet identifiant.'));
            }
        } elseif ($secteur_id !== null) {
            $result = $projection->readBySecteur();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des projections', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'success', 'message' => 'Aucune projection trouvée.', 'data' => []));
            }
        } else {
            $result = $projection->readAll();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des projections', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'success', 'message' => 'Aucune projection trouvée.', 'data' => []));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $projection->id = $id;
            $projection->referentiel_id = sanitize_input($_POST['referentiel_id']);
            $projection->secteur_id = sanitize_input($_POST['secteur_id']);
            $projection->scenario = sanitize_input($_POST['scenario']);
            $projection->annee = sanitize_input($_POST['annee']);
            $projection->valeur = sanitize_input($_POST['valeur']);
            $projection->unite = sanitize_input($_POST['unite'] ?? '');
            $projection->source = sanitize_input($_POST['source'] ?? '');
            $projection->description = sanitize_input($_POST['description'] ?? '');
            $projection->state = sanitize_input($_POST['state'] ?? 'actif');
            $projection->add_by = $payload['user_id'] ?? null;

            if (empty($projection->secteur_id) || empty($projection->scenario) || empty($projection->annee) || empty($projection->valeur)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs obligatoires !!!'));
                exit();
            }

            if ($projection->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Projection modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la projection.'));
            }
        } else {
            $projection->referentiel_id = sanitize_input($_POST['referentiel_id']);
            $projection->secteur_id = sanitize_input($_POST['secteur_id']);
            $projection->scenario = sanitize_input($_POST['scenario']);
            $projection->annee = sanitize_input($_POST['annee']);
            $projection->valeur = sanitize_input($_POST['valeur']);
            $projection->unite = sanitize_input($_POST['unite'] ?? '');
            $projection->source = sanitize_input($_POST['source'] ?? '');
            $projection->description = sanitize_input($_POST['description'] ?? '');
            $projection->state = sanitize_input($_POST['state'] ?? 'actif');
            $projection->add_by = $payload['user_id'] ?? null;

            if (empty($projection->secteur_id) || empty($projection->scenario) || empty($projection->annee) || empty($projection->valeur)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs obligatoires !!!'));
                exit();
            }

            // Check if projection already exists (unique constraint)
            $existing = $projection->read();
            $existing = array_filter($existing, function ($item) use ($projection) {
                return $item['secteur_id'] == $projection->secteur_id && $item['scenario'] == $projection->scenario && $item['annee'] == $projection->annee;
            });
            if ($existing) {
                echo json_encode(array(
                    'status' => 'warning',
                    'message' => 'Une projection existe déjà pour ce secteur, scénario et année.',
                    'existing_id' => $existing['id']
                ));
                exit();
            }

            if ($projection->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Projection créée avec succès.', 'id' => $projection->id));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la projection.'));
            }
        }
        break;

    case 'PUT':
        $projection->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        if (isset($state) && $projection->updateState($state)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Statut de la projection modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du statut de la projection.'));
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $secteur_id = isset($_GET['secteur']) ? sanitize_input($_GET['secteur']) : null;
        $scenario = isset($_GET['scenario']) ? sanitize_input($_GET['scenario']) : null;

        if ($id) {
            $projection->id = $id;
            $projection->delete();
            echo json_encode(array('status' => 'success', 'message' => 'Projection supprimée avec succès.'));
        } elseif ($secteur_id && $scenario) {
            $projection->secteur_id = $secteur_id;
            $projection->scenario = $scenario;
            $projection->deleteLine();
            echo json_encode(array('status' => 'success', 'message' => 'Projection supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la projection.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requête non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();
