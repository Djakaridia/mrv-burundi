<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/TacheIndicateur.php';

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
$tacheIndicateur = new TacheIndicateur($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if (!$id) {
            // Create
            $tacheIndicateur->code = sanitize_input($_POST['code']);
            $tacheIndicateur->name = sanitize_input($_POST['name']);
            $tacheIndicateur->unite = sanitize_input($_POST['unite']);
            $tacheIndicateur->valeur_cible = sanitize_input($_POST['valeur_cible']);
            $tacheIndicateur->description = sanitize_input($_POST['description']);
            $tacheIndicateur->tache_id = sanitize_input($_POST['tache_id']);
            $tacheIndicateur->add_by = sanitize_input($payload['user_id']);

            if (empty($tacheIndicateur->name) || empty($tacheIndicateur->code) || empty($tacheIndicateur->tache_id)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                exit();
            }

            if ($tacheIndicateur->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Tache indicateur créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la tache indicateur.'));
            }
        } else {
            // Update
            $tacheIndicateur->id = sanitize_input($_GET['id']);
            $tacheIndicateur->name = sanitize_input($_POST['name']);
            $tacheIndicateur->code = sanitize_input($_POST['code']);
            $tacheIndicateur->description = sanitize_input($_POST['description']);
            $tacheIndicateur->unite = sanitize_input($_POST['unite']);
            $tacheIndicateur->valeur_cible = sanitize_input($_POST['valeur_cible']);
            $tacheIndicateur->tache_id = sanitize_input($_POST['tache_id']);
            $tacheIndicateur->add_by = sanitize_input($payload['user_id']);

            if ($tacheIndicateur->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Tache indicateur modifiée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la tache indicateur.'));
            }
        }
        break;

    case 'DELETE':
        $tacheIndicateur->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($tacheIndicateur->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Tache indicateur supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la tache indicateur.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $tache_id = isset($_GET['tache_id']) ? sanitize_input($_GET['tache_id']) : null;

        if ($id) {
            $tacheIndicateur->id = $id;
            $result = $tacheIndicateur->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de suivi de tâche', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun suivi  trouvée avec cet identifiant.'));
            }
        } else if ($tache_id) {
            $tacheIndicateur->tache_id = $tache_id;
            $result = $tacheIndicateur->readByTache();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Tâches du projet', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune tâche trouvée pour cette tache .'));
            }
        } else {
            $result = $tacheIndicateur->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des tâches', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune tâche trouvée.'));
            }
        }
        break;

    default:
        echo json_encode(array("message" => "Method not allowed."));
        break;
}

// Close database connection
$db = null;
exit();
