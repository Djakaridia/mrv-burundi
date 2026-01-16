<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/RapportPeriode.php';
require_once $routePath . 'models/Projet.php';
require_once $routePath . 'models/Structure.php';
require_once $routePath . 'services/projet.mailer.php';

configureCORS();
loadVarEnv();
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
$rapport = new RapportPeriode($db);
$sendmailer = new ProjetMailer($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_USERNAME'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);
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
            $rapport->id = $id;
            $result = $rapport->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données du rapport', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun rapport trouvé avec cet identifiant.'));
            }
        } else {
            $result = $rapport->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des rapports', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun rapport trouvé.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $rapport->id = $id;
            $rapport->code = sanitize_input($_POST['code']);
            $rapport->intitule = sanitize_input($_POST['intitule']);
            $rapport->periode = sanitize_input($_POST['periode']);
            $rapport->mois_ref = sanitize_input($_POST['mois_ref']);
            $rapport->annee_ref = sanitize_input($_POST['annee_ref']);
            $rapport->description = sanitize_input($_POST['description']);
            $rapport->projet_id = sanitize_input($_POST['projet_id']);
            $rapport->add_by = sanitize_input($payload['user_id']);

            if (empty($rapport->code) || empty($rapport->intitule)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($rapport->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Rapport modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du rapport.'));
            }
        } else {
            $rapport->code = sanitize_input($_POST['code']);
            $rapport->intitule = sanitize_input($_POST['intitule']);
            $rapport->periode = sanitize_input($_POST['periode']);
            $rapport->mois_ref = sanitize_input($_POST['mois_ref']);
            $rapport->annee_ref = sanitize_input($_POST['annee_ref']);
            $rapport->description = sanitize_input($_POST['description']);
            $rapport->projet_id = sanitize_input($_POST['projet_id']);
            $rapport->add_by = sanitize_input($payload['user_id']);

            if (empty($rapport->code) || empty($rapport->intitule)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($rapport->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Rapport créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du rapport.'));
            }
        }
        break;

    case 'PUT':
        $rapport->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        $rapport_data = $rapport->readById();

        // Get projet data
        $projet = new Projet($db);
        $projet->id = $rapport_data['projet_id'];
        $projet_data = $projet->readById();

        // Get structure data
        $structure = new Structure($db);
        $structure->id = $projet_data['structure_id'];
        $structure_data = $structure->readById();

        if (isset($state) && $rapport->updateState($state)) {
            if ($state == 'valided') {
                $sendmailer->sendValideReport($structure_data['email'], $structure_data['sigle'], $rapport_data['intitule'], $projet_data['name']);
            }
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Rapport modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du Rapport.'));
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $rapport->id = $id;

        if ($rapport->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Rapport supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du rapport.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();