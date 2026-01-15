<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Typologie.php';

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
$typologie = new Typologie($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return htmlspecialchars(trim($data));
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $typologie->id = $id;
            $result = $typologie->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la typologie', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune typologie trouvée avec cet identifiant.'));
            }
        } else {
            $referentiel_id = isset($_GET['referentiel_id']) ? sanitize_input($_GET['referentiel_id']) : null;
            if ($referentiel_id) {
                $typologie->referentiel_id = $referentiel_id;
                $result = $typologie->readByReferentielId();
                echo json_encode(array('status' => 'success', 'message' => 'Données des typologies', 'data' => $result));
            } else {
                $result = $typologie->read();
                echo json_encode(array('status' => 'success', 'message' => 'Données des typologies', 'data' => $result));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $typologie->id = $id;
            $typologie->name = sanitize_input($_POST['name']);
            $typologie->couleur = sanitize_input($_POST['couleur']);
            $typologie->referentiel_id = sanitize_input($_POST['referentiel_id']);
            $typologie->add_by = sanitize_input($payload['user_id']);

            if (empty($typologie->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($typologie->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Typologie modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la typologie.'));
            }
        } else {
            $typologie->name = sanitize_input($_POST['name']);
            $typologie->couleur = sanitize_input($_POST['couleur']);
            $typologie->referentiel_id = sanitize_input($_POST['referentiel_id']);
            $typologie->add_by = sanitize_input($payload['user_id']);

            if (empty($typologie->name)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($typologie->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Typologie créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la typologie.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $typologie->id = $id;

        if ($typologie->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Typologie supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la typologie.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();
