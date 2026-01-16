<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Tache.php';
require_once $routePath . 'models/SuiviTache.php';

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
$tache = new Tache($db);
$suivi_tache = new SuiviTache($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $tache_id = isset($_GET['tache_id']) ? sanitize_input($_GET['tache_id']) : null;

        if ($id) {
            $suivi_tache->id = $id;
            $result = $suivi_tache->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de suivi de tâche', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun suivi  trouvée avec cet identifiant.'));
            }
        } else if ($tache_id) {
            $suivi_tache->tache_id = $tache_id;
            $result = $suivi_tache->readByTache();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Tâches du projet', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune tâche trouvée pour cette tache .'));
            }
        } else {
            $result = $suivi_tache->read();
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
            $suivi_tache->id = $id;
            $suivi_tache->name = sanitize_input($_POST['name']);
            $suivi_tache->code = sanitize_input($_POST['code']);
            $suivi_tache->description = sanitize_input($_POST['description']);
            $suivi_tache->etat_avancement = sanitize_input($_POST['etat_avancement']);
            $suivi_tache->difficulte = sanitize_input($_POST['difficulte']);
            $suivi_tache->solution = sanitize_input($_POST['solution']);
            $suivi_tache->date_suivi = sanitize_input($_POST['date_suivi']);
            $suivi_tache->add_by = sanitize_input($payload['user_id']);
            $suivi_tache->tache_id = sanitize_input($_POST['tache_id']);
            $suivi_tache->status = isset($_POST['status']) ? sanitize_input($_POST['status']) : 'En cours';

            if (empty($suivi_tache->name) || empty($suivi_tache->code) || empty($suivi_tache->tache_id)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs obligatoires !!!'));
                exit();
            }

            if ($suivi_tache->update()) {
                $tache->id = $suivi_tache->tache_id;
                $tache->updateStatus($suivi_tache->status);

                echo json_encode(array('status' => 'success', 'message' => 'Tâche modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la tâche.'));
            }
        } else {
            $suivi_tache->name = sanitize_input($_POST['name']);
            $suivi_tache->code = sanitize_input($_POST['code']);
            $suivi_tache->description = sanitize_input($_POST['description']);
            $suivi_tache->etat_avancement = sanitize_input($_POST['etat_avancement']);
            $suivi_tache->difficulte = sanitize_input($_POST['difficulte']);
            $suivi_tache->solution = sanitize_input($_POST['solution']);
            $suivi_tache->date_suivi = sanitize_input($_POST['date_suivi']);
            $suivi_tache->add_by = sanitize_input($payload['user_id']);
            $suivi_tache->tache_id = sanitize_input($_POST['tache_id']);
            $suivi_tache->status = isset($_POST['status']) ? sanitize_input($_POST['status']) : 'En cours';

            if (empty($suivi_tache->name) || empty($suivi_tache->code) || empty($suivi_tache->tache_id)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs obligatoires !!!'));
                exit();
            }

            if ($suivi_tache->create()) {
                $tache->id = $suivi_tache->tache_id;
                $tache->updateStatus($suivi_tache->status);

                echo json_encode(array('status' => 'success', 'message' => 'Tâche créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la tâche.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $suivi_tache->id = $id;

        if ($suivi_tache->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Tâche supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de suivi de tache.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();