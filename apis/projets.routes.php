<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/Projet.php';
require_once $routePath . 'models/Structure.php';
require_once $routePath . 'models/GroupeUsers.php';
require_once $routePath . 'models/User.php';
require_once $routePath . 'services/projet.mailer.php';
require_once $routePath . 'services/projet.green.php';
require_once $routePath . 'services/notify-push.php';

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
$projet = new Projet($db);
$sendmailer = new ProjetMailer($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_USERNAME'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);
$sendgreen = new ProjetGreen($_ENV['API_GREEN_INSTANCE'], $_ENV['API_GREEN_TOKEN'], $_ENV['API_GREEN_IND']);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uploadDirectory = $routePath . 'uploads/';

// Input sanitization function
function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $projet->id = $id;
            $result = $projet->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données du projet', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun projet trouvé avec cet identifiant.'));
            }
        } else {
            $result = $projet->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des projets', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun projet trouvé.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            if (isset($_FILES['file'])) {
                $projet->logo = uploadFile($_FILES['file'], $_POST['allow_files'], $uploadDirectory);
            } else {
                $projet->logo = sanitize_input($_POST['logo']);
            }

            $projet->id = $id;
            $projet->name = sanitize_input($_POST['name']);
            $projet->code = sanitize_input($_POST['code']);
            $projet->description = $_POST['description'] ?? '';
            $projet->objectif = $_POST['objectif'] ?? '';
            $projet->status = sanitize_input($_POST['status'] ?? 'planifie');
            $projet->budget = floatval($_POST['budget'] ?? 0);
            $projet->start_date = sanitize_input($_POST['start_date'] ?? null);
            $projet->end_date = sanitize_input($_POST['end_date'] ?? null);
            $projet->signature_date = sanitize_input($_POST['signature_date'] ?? null);
            $projet->miparcours_date = sanitize_input($_POST['miparcours_date'] ?? null);
            $projet->structure_id = sanitize_input($_POST['structure_id']);
            $projet->action_type = sanitize_input($_POST['action_type']);
            $projet->gaz_type = sanitize_input($_POST['gaz_type']);
            $projet->secteurs = isset($_POST['secteurs']) ? json_encode($_POST['secteurs']) : null;
            $projet->groupes = isset($_POST['groupes']) ? json_encode($_POST['groupes']) : null;
            $projet->programmes = isset($_POST['programmes']) ? json_encode($_POST['programmes']) : null;
            $projet->add_by = $payload['user_id'] ?? null;

            if (empty($projet->code) || empty($projet->name) || empty($projet->structure_id)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs obligatoires !!!'));
                exit();
            }

            if ($projet->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Projet modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du projet.'));
            }
        } else {
            $projet->logo = "";
            if (isset($_FILES['file'])) {
                $projet->logo = uploadFile($_FILES['file'], $_POST['allow_files'], $uploadDirectory);
            } else {
                $projet->logo = sanitize_input($_POST['logo']);
            }

            $projet->name = sanitize_input($_POST['name']);
            $projet->code = sanitize_input($_POST['code']);
            $projet->description = $_POST['description'] ?? '';
            $projet->objectif = $_POST['objectif'] ?? '';
            $projet->status = sanitize_input($_POST['status'] ?? 'planifie');
            $projet->budget = floatval($_POST['budget'] ?? 0);
            $projet->start_date = sanitize_input($_POST['start_date'] ?? null);
            $projet->end_date = sanitize_input($_POST['end_date'] ?? null);
            $projet->signature_date = sanitize_input($_POST['signature_date'] ?? null);
            $projet->miparcours_date = sanitize_input($_POST['miparcours_date'] ?? null);
            $projet->structure_id = sanitize_input($_POST['structure_id']);
            $projet->action_type = sanitize_input($_POST['action_type']);
            $projet->gaz_type = sanitize_input($_POST['gaz_type']);
            $projet->secteurs = isset($_POST['secteurs']) ? json_encode($_POST['secteurs']) : null;
            $projet->groupes = isset($_POST['groupes']) ? json_encode($_POST['groupes']) : null;
            $projet->programmes = isset($_POST['programmes']) ? json_encode($_POST['programmes']) : null;
            $projet->add_by = $payload['user_id'] ?? null;

            // Get Structure Data
            $structure = new Structure($db);
            $structure->id = $projet->structure_id;
            $structure_data = $structure->readById();

            // Extraire les ids des groupes
            $groupes_ids = explode(',', str_replace('"', '', $projet->groupes));
            $groupe_user = new GroupeUsers($db);
            $groupes_users = $groupe_user->read();
            $groupes_users = array_filter($groupes_users, function ($groupe_user) use ($groupes_ids) {
                return in_array($groupe_user['groupe_id'], $groupes_ids);
            });

            // Extraire les ids des utilisateurs
            $users_ids = array_map(function ($groupe_user) { return $groupe_user['user_id']; }, $groupes_users);
            $user = new User($db);
            $users = $user->read();
            $users = array_filter($users, function ($user) use ($users_ids) {
                return in_array($user['id'], $users_ids);
            });

            if (empty($projet->code) || empty($projet->name) || empty($projet->structure_id)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs obligatoires !!!'));
                exit();
            }

            if ($projet->create()) {
                $notifResult = addProjetNotif($db, $projet->name, $projet->structure_id, $payload);
                $sendmailer->sendAffectStructure($structure_data['email'], $structure_data['sigle'], $projet->name);

                foreach ($users as $user) {
                    $sendgreen->sendAssignProject($user['phone'], $user['username'], $projet->name);
                }

                echo json_encode(array('status' => 'success', 'message' => 'Projet créé avec succès.', 'notifications' => $notifResult));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du projet.'));
            }
        }
        break;

    case 'PUT':
        $projet->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        if (isset($state) && $projet->updateState($state)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Projet modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du Projet.'));
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $projet->id = $id;
        $projetData = $projet->readById();

        if ($projet->delete()) {
            deleteFile($projetData['logo']);
            echo json_encode(array('status' => 'success', 'message' => 'Projet supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du projet.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();
