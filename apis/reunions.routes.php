<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/Reunions.php';
require_once $routePath . 'models/GroupeTravail.php';
require_once $routePath . 'models/GroupeUsers.php';
require_once $routePath . 'models/User.php';
require_once $routePath . 'services/notify-push.php';
require_once $routePath . 'services/groupe.mailer.php';
require_once $routePath . 'services/groupe.green.php';

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
$reunions = new Reunion($db);
$sendmailer = new GroupeMailer($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_USERNAME'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);
$sendgreen = new GroupeGreen($_ENV['API_GREEN_INSTANCE'], $_ENV['API_GREEN_TOKEN'], $_ENV['API_GREEN_IND']);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $reunions->id = $id;
            $data = $reunions->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la réunion', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune donnée trouvée pour l\'ID ' . $id));
            }
        } else {
            $data = $reunions->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des réunions', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune réunion trouvée'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id === null) {
            // Create
            $reunions->groupe_id = sanitize_input($_POST['groupe_id']);
            $reunions->code = sanitize_input($_POST['code']);
            $reunions->name = sanitize_input($_POST['name']);
            $reunions->description = sanitize_input($_POST['description']);
            $reunions->horaire = sanitize_input($_POST['horaire']);
            $reunions->couleur = sanitize_input($_POST['couleur']);
            $reunions->lieu = sanitize_input($_POST['lieu']);
            $reunions->status = "planifie";
            $reunions->add_by = sanitize_input($payload['user_id']);

            // Get Groupe Data
            $groupe = new GroupeTravail($db);
            $groupe->id = $reunions->groupe_id;
            $groupe_data = $groupe->readById();

            // Get Structure Data
            $structure = new Structure($db);
            $structure->id = $groupe_data['monitor'];
            $structure_data = $structure->readById();

            // Get Groupe Users Data
            $groupe_users = new GroupeUsers($db);
            $groupe_users->groupe_id = $reunions->groupe_id;
            $groupe_users_data = $groupe_users->readByGroupeId();

            // Mapping IDs users
            $members = array();
            foreach ($groupe_users_data as $groupe_users_data) {
                $members[] = $groupe_users_data['user_id'];
            }

            // Get Users Data
            $users = new User($db);
            $users_data = $users->read();
            $users_members = array_filter($users_data, function ($user) use ($members) {
                return in_array($user['id'], $members);
            });

            if (empty($reunions->groupe_id)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs.'));
                exit();
            }

            if ($reunions->create()) {
                $notifResult = addReunionNotif($db, $reunions->name, $reunions->groupe_id, $payload);
                $sendmailer->sendAddMeet($structure_data['email'], $structure_data['sigle'], $reunions->name, $reunions->horaire);

                foreach ($users_members as $user) {
                    $sendgreen->sendNewMeet($user['phone'], $user['username'], $reunions->name, $reunions->horaire, $reunions->description);
                }

                echo json_encode(array('status' => 'success', 'message' => 'Réunion créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la réunion.'));
            }
        } else {
            // Update
            $reunions->id = $id;
            $reunions->groupe_id = sanitize_input($_POST['groupe_id']);
            $reunions->code = sanitize_input($_POST['code']);
            $reunions->name = sanitize_input($_POST['name']);
            $reunions->description = sanitize_input($_POST['description']);
            $reunions->horaire = sanitize_input($_POST['horaire']);
            $reunions->couleur = sanitize_input($_POST['couleur']);
            $reunions->lieu = sanitize_input($_POST['lieu']);
            $reunions->status = sanitize_input($_POST['status']);
            $reunions->add_by = sanitize_input($payload['user_id']);

            if ($reunions->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Réunion modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la réunion.'));
            }
        }
        break;

    case 'PUT':
        $reunions->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        if (isset($state) && $reunions->updateState($state)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Réunion modifiée avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la Réunion.'));
        }
        break;

    case 'DELETE':
        $reunions->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        if ($reunions->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Réunion supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la réunion.'));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}

// Close database connection
$db = null;
exit();
