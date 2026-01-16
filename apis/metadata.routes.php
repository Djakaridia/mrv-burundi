<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Metadata.php';

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
$metadata = new Metadata($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $referentiel_id = isset($_GET['referentiel_id']) ? sanitize_input($_GET['referentiel_id']) : null;
        if ($id) {
            $metadata->id = $id;
            $result = $metadata->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la metadata', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune metadata trouvée avec cet identifiant.'));
            }
        } else {
            if ($referentiel_id) {
                $metadata->referentiel_id = $referentiel_id;
                $result = $metadata->readByReferentielId();
                if ($result) {
                    echo json_encode(array('status' => 'success', 'message' => 'Données des metadata', 'data' => $result));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Aucune metadata trouvée.'));
                }
            } else {
                $result = $metadata->read();
                if ($result) {
                    echo json_encode(array('status' => 'success', 'message' => 'Données des metadata', 'data' => $result));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Aucune metadata trouvée.'));
                }
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $metadata->id = $id;
            $metadata->source = sanitize_input($_POST['source']);
            $metadata->date_ref = sanitize_input($_POST['date_ref']);
            $metadata->description = $_POST['description'];
            $metadata->referentiel_id = sanitize_input($_POST['referentiel_id']);
            $metadata->add_by = sanitize_input($payload['user_id']);

            if (empty($metadata->source) || empty($metadata->referentiel_id)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($metadata->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Metadata modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la metadata.'));
            }
        } else {
            $metadata->source = sanitize_input($_POST['source']);
            $metadata->date_ref = sanitize_input($_POST['date_ref']);
            $metadata->description = $_POST['description'];
            $metadata->referentiel_id = sanitize_input($_POST['referentiel_id']);
            $metadata->add_by = sanitize_input($payload['user_id']);

            if (empty($metadata->source) || empty($metadata->referentiel_id)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($metadata->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Metadata créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la metadata.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $metadata->id = $id;

        if ($metadata->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Metadata supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la metadata.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();
