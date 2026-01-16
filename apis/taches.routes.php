<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/Tache.php';
require_once $routePath . 'models/Projet.php';
require_once $routePath . 'models/Structure.php';
require_once $routePath . 'models/User.php';
require_once $routePath . 'services/projet.green.php';
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
$tache = new Tache($db);
$sendmailer = new ProjetMailer($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_USERNAME'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);
$sendgreen = new ProjetGreen($_ENV['API_GREEN_INSTANCE'], $_ENV['API_GREEN_TOKEN'], $_ENV['API_GREEN_IND']);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $projet_id = isset($_GET['projet_id']) ? sanitize_input($_GET['projet_id']) : null;

        if ($id) {
            $tache->id = $id;
            $result = $tache->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la tâche', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune tâche trouvée avec cet identifiant.'));
            }
        } else if ($projet_id) {
            $tache->projet_id = $projet_id;
            $result = $tache->readByProjet();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Tâches du projet', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune tâche trouvée pour ce projet.'));
            }
        } else {
            $result = $tache->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des tâches', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune tâche trouvée.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $tache->id = $id;
            $tache->name = sanitize_input($_POST['name']);
            $tache->code = sanitize_input($_POST['code']);
            $tache->description = sanitize_input($_POST['description']);
            $tache->status = sanitize_input($_POST['status']);
            $tache->debut_prevu = sanitize_input($_POST['debut_prevu']);
            $tache->fin_prevue = sanitize_input($_POST['fin_prevue']);
            $tache->projet_id = sanitize_input($_POST['projet_id']);
            $tache->assigned_id = sanitize_input($_POST['assigned_id']);
            $tache->priorites_id = sanitize_input($_POST['priorites_id']);
            $tache->add_by = sanitize_input($payload['user_id']);

            if (empty($tache->name) || empty($tache->code) || empty($tache->projet_id) || empty($tache->assigned_id) || empty($tache->priorites_id)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs obligatoires !!!'));
                exit();
            }

            if ($tache->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Tâche modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la tâche.'));
            }
        } else {
            $tache->name = sanitize_input($_POST['name']);
            $tache->code = sanitize_input($_POST['code']);
            $tache->description = sanitize_input($_POST['description']);
            $tache->status = sanitize_input($_POST['status']);
            $tache->debut_prevu = sanitize_input($_POST['debut_prevu']);
            $tache->fin_prevue = sanitize_input($_POST['fin_prevue']);
            $tache->projet_id = sanitize_input($_POST['projet_id']);
            $tache->assigned_id = sanitize_input($_POST['assigned_id']);
            $tache->priorites_id = sanitize_input($_POST['priorites_id']);
            $tache->add_by = sanitize_input($payload['user_id']);

            // Get projet data
            $projet = new Projet($db);
            $projet->id = $tache->projet_id;
            $projet_data = $projet->readById();

            // Get structure data
            $structure = new Structure($db);
            $structure->id = $projet_data['structure_id'];
            $structure_data = $structure->readById();

            // Get assigned user data
            $assigned = new User($db);
            $assigned->id = $tache->assigned_id;
            $assigned_data = $assigned->readById();

            if (empty($tache->name) || empty($tache->code) || empty($tache->projet_id) || empty($tache->assigned_id) || empty($tache->priorites_id)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs obligatoires !!!'));
                exit();
            }

            if ($tache->create()) {
                $sendmailer->sendAddTask($structure_data['email'], $structure_data['sigle'], $tache->name, $projet_data['name'], $projet_data['id']);
                $sendgreen->sendAssignTask($assigned_data['phone'], $assigned_data['username'], $tache->name);

                echo json_encode(array('status' => 'success', 'message' => 'Tâche créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la tâche.'));
            }
        }
        break;

    case 'PUT':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $tache->id = $id;
        $state = sanitize_input($_GET['state']);

        if ($tache->updateState($state)) {
            echo json_encode(array('status' => 'success', 'message' => 'Tâche modifiée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la tâche.'));
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $tache->id = $id;

        if ($tache->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Tâche supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la tâche.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();