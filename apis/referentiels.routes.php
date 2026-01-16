<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Referentiel.php';

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
$referentiel = new Referentiel($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $referentiel->id = $id;
            $result = $referentiel->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données du référentiel', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun référentiel trouvé avec cet identifiant.'));
            }
        } else {
            $result = $referentiel->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des référentiels', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun référentiel trouvé.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $referentiel->id = $id;
            $referentiel->code = sanitize_input($_POST['code']);
            $referentiel->intitule = sanitize_input($_POST['intitule']);
            $referentiel->description = sanitize_input($_POST['description']);
            $referentiel->categorie = sanitize_input($_POST['categorie']);
            $referentiel->unite = sanitize_input($_POST['unite']);
            $referentiel->echelle = sanitize_input($_POST['echelle']);
            $referentiel->modele = sanitize_input($_POST['modele']);
            $referentiel->domaine = sanitize_input($_POST['domaine']);
            $referentiel->action = sanitize_input($_POST['action']);
            $referentiel->responsable = sanitize_input($_POST['responsable']);
            $referentiel->autre_responsable = sanitize_input($_POST['autre_responsable']);
            $referentiel->fonction_agregation = sanitize_input($_POST['fonction_agregation']);
            $referentiel->sens_evolution = sanitize_input($_POST['sens_evolution']);
            $referentiel->seuil_min = sanitize_input($_POST['seuil_min'] ?? "");
            $referentiel->seuil_max = sanitize_input($_POST['seuil_max'] ?? "");
            $referentiel->norme = sanitize_input($_POST['norme'] ?? "");
            $referentiel->in_dashboard = sanitize_input($_POST['in_dashboard'] ?? 0);
            $referentiel->add_by = sanitize_input($payload['user_id']);

            if ($referentiel->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Référentiel modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du référentiel.'));
            }
        } else {
            $referentiel->code = sanitize_input($_POST['code']);
            $referentiel->intitule = sanitize_input($_POST['intitule']);
            $referentiel->description = sanitize_input($_POST['description']);
            $referentiel->categorie = sanitize_input($_POST['categorie']);
            $referentiel->unite = sanitize_input($_POST['unite']);
            $referentiel->echelle = sanitize_input($_POST['echelle']);
            $referentiel->modele = sanitize_input($_POST['modele']);
            $referentiel->domaine = sanitize_input($_POST['domaine']);
            $referentiel->action = sanitize_input($_POST['action']);
            $referentiel->responsable = sanitize_input($_POST['responsable']);
            $referentiel->autre_responsable = sanitize_input($_POST['autre_responsable']);
            $referentiel->fonction_agregation = sanitize_input($_POST['fonction_agregation']);
            $referentiel->sens_evolution = sanitize_input($_POST['sens_evolution']);
            $referentiel->seuil_min = sanitize_input($_POST['seuil_min']??"");
            $referentiel->seuil_max = sanitize_input($_POST['seuil_max']??"");
            $referentiel->norme = sanitize_input($_POST['norme']??"");
            $referentiel->in_dashboard = sanitize_input($_POST['in_dashboard'] ?? 0);
            $referentiel->add_by = sanitize_input($payload['user_id']);

            if (
                empty($referentiel->intitule) || empty($referentiel->code) || empty($referentiel->categorie) || empty($referentiel->unite)
                || empty($referentiel->echelle) || empty($referentiel->modele) || empty($referentiel->domaine) || empty($referentiel->responsable)
            ) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs obligatoires !!!'));
                exit();
            }

            if ($referentiel->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Référentiel créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du référentiel.'));
            }
        }
        break;

    case 'PUT':
        $referentiel->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        if (isset($state) && $referentiel->updateState($state)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Indicateur modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'Indicateur.'));
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $referentiel->id = $id;

        if ($referentiel->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Référentiel supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du référentiel.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();
