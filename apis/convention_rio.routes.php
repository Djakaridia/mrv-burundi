<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/ConventionRIO.php';

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
$convention_rio = new ConventionRIO($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $referentiel_id = isset($_GET['referentiel_id']) ? sanitize_input($_GET['referentiel_id']) : null;
        if ($id) {
            $convention_rio->id = $id;
            $result = $convention_rio->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la convention RIO', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune convention RIO trouvée avec cet identifiant.'));
            }
        } else {
            if ($referentiel_id) {
                $convention_rio->referentiel_id = $referentiel_id;
                $result = $convention_rio->readByReferentielId();
                if ($result) {
                    echo json_encode(array('status' => 'success', 'message' => 'Données des conventions RIO', 'data' => $result));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Aucune convention RIO trouvée.'));
                }
            } else {
                $result = $convention_rio->read();
                if ($result) {
                    echo json_encode(array('status' => 'success', 'message' => 'Données des conventions RIO', 'data' => $result));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Aucune convention RIO trouvée.'));
                }
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $convention_rio->id = $id;
            $convention_rio->code = sanitize_input($_POST['code']);
            $convention_rio->programme = sanitize_input($_POST['programme']);
            $convention_rio->niveau = sanitize_input($_POST['niveau']);
            $convention_rio->referentiel_id = sanitize_input($_POST['referentiel_id']);
            $convention_rio->add_by = sanitize_input($payload['user_id']);

            if (empty($convention_rio->code) || empty($convention_rio->referentiel_id)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($convention_rio->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Convention RIO modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la convention RIO.'));
            }
        } else {
            $convention_rio->code = sanitize_input($_POST['code']);
            $convention_rio->programme = sanitize_input($_POST['programme']);
            $convention_rio->niveau = sanitize_input($_POST['niveau']);
            $convention_rio->referentiel_id = sanitize_input($_POST['referentiel_id']);
            $convention_rio->add_by = sanitize_input($payload['user_id']);

            if (empty($convention_rio->code) || empty($convention_rio->referentiel_id)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($convention_rio->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Convention RIO créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la convention RIO.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $convention_rio->id = $id;

        if ($convention_rio->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Convention RIO supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la convention RIO.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();
