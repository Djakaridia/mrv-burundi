<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/TacheCout.php';

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
$tacheCout = new TacheCout($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'POST':
        try {
            // Validation des données requises
            $requiredFields = ['tache_id', 'valeur_couts'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['status' => 'danger', 'message' => "Le champ $field est requis"]);
                    exit();
                }
            }

            // Nettoyage des données
            $tache_id = sanitize_input($_POST['tache_id']);
            $add_by = sanitize_input($payload['user_id']);
            $coutsData = json_decode($_POST['valeur_couts'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['status' => 'danger', 'message' => "Format JSON invalide pour les couts"]);
                exit();
            }

            // Suppression des anciennes couts
            $tacheCout->tache_id = $tache_id;
            if (!$tacheCout->delete()) {
                echo json_encode(['status' => 'danger', 'message' => "Erreur lors de la suppression des anciennes couts"]);
                exit();
            }

            // Insertion des nouvelles couts
            $successCount = 0;
            $errors = [];
            foreach ($coutsData as $cout) {

                if (empty($cout['montant'])) {
                    $errors[] = "Données manquantes pour la convention " . $cout['convention'];
                    continue;
                }

                $tacheCout->montant = sanitize_input($cout['montant']);
                $tacheCout->convention = sanitize_input($cout['convention']);
                $tacheCout->tache_id = $tache_id;
                $tacheCout->add_by = $add_by;

                if ($tacheCout->create()) {
                    $successCount++;
                } else {
                    $errors[] = "Erreur lors de la création de la cout pour la convention " . $cout['convention'];
                }
            }

            if (empty($errors)) {
                echo json_encode(['status' => 'success', 'message' => "Toutes les couts ($successCount) ont été créées avec succès", 'count' => $successCount]);
            } else {
                echo json_encode(['status' => 'success', 'message' => "$successCount couts créées, mais avec certaines erreurs", 'count' => $successCount, 'errors' => $errors]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $tacheCout->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($tacheCout->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Tache cout supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la tache cout.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $tache_id = isset($_GET['tache_id']) ? sanitize_input($_GET['tache_id']) : null;

        if ($id) {
            $tacheCout->id = $id;
            $result = $tacheCout->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de suivi de cout', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun suivi  trouvée avec cet identifiant.'));
            }
        } else if ($tache_id) {
            $tacheCout->tache_id = $tache_id;
            $result = $tacheCout->readByTache();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Couts de la tâche', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun cout trouvée pour cette tache .'));
            }
        } else {
            $result = $tacheCout->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des couts', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun cout trouvée.'));
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
