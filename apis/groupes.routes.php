<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/GroupeTravail.php';
require_once $routePath . 'models/Structure.php';
require_once $routePath . 'services/groupe.mailer.php';

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

$database = new Database();
$db = $database->getConnection();
$groupeTravail = new GroupeTravail($db);
$sendmailer = new GroupeMailer($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_USERNAME'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id === null) {
            // Create
            $groupeTravail->code = sanitize_input($_POST['code']);
            $groupeTravail->name = sanitize_input($_POST['name']);
            $groupeTravail->monitor = sanitize_input($_POST['monitor']);
            $groupeTravail->description = sanitize_input($_POST['description']);

            if (empty($groupeTravail->name) || empty($groupeTravail->monitor)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs.'));
                exit();
            }

            if ($groupeTravail->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Espaces de travail créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du espaces de travail.'));
            }
        } else {
            // Update
            $groupeTravail->id = $id;
            $groupeTravail->code = sanitize_input($_POST['code']);
            $groupeTravail->name = sanitize_input($_POST['name']);
            $groupeTravail->monitor = sanitize_input($_POST['monitor']);
            $groupeTravail->description = sanitize_input($_POST['description']);

            if ($groupeTravail->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Espaces de travail modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du espaces de travail.'));
            }
        }
        break;

    case 'PUT':
        $groupeTravail->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);
        $groupe_data = $groupeTravail->readById();

        $structure = new Structure($db);
        $structure->id = $groupe_data['monitor'];
        $structure_data = $structure->readById();

        if (isset($state) && $groupeTravail->updateState($state)) {
            if ($state == 'inactif') {
                $sendmailer->sendClosed($structure_data['email'], $structure_data['sigle'], $groupe_data['name']);
            }
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Groupe modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du Groupe.'));
        }
        break;

    case 'DELETE':
        $groupeTravail->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($groupeTravail->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Espaces de travail supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du espaces de travail.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $groupeTravail->id = $id;
            $data = $groupeTravail->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Données du espaces de travail', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune donnée trouvée pour l\'ID ' . $id));
            }
        } else {
            $data = $groupeTravail->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des groupes de travail', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun espaces de travail trouvé'));
            }
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Méthode non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();
