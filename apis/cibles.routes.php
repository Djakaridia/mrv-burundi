<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Cible.php';

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
$cible = new Cible($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

switch ($requestMethod) {
    case 'POST':
        try {
            // Validation des données requises
            $requiredFields = ['cmr_id', 'projet_id', 'valeur_cibles'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['status' => 'danger', 'message' => "Le champ $field est requis"]);
                    exit();
                }
            }

            // Nettoyage des données
            $cmr_id = sanitize_input($_POST['cmr_id']);
            $projet_id = sanitize_input($_POST['projet_id']);
            $add_by = sanitize_input($payload['user_id']);
            $ciblesData = json_decode($_POST['valeur_cibles'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['status' => 'danger', 'message' => "Format JSON invalide pour les cibles"]);
                exit();
            }

            // Suppression des anciennes cibles
            $cible->cmr_id = $cmr_id;
            if (!$cible->delete()) {
                echo json_encode(['status' => 'danger', 'message' => "Erreur lors de la suppression des anciennes cibles"]);
                exit();
            }

            // Insertion des nouvelles cibles
            $successCount = 0;
            $errors = [];
            foreach ($ciblesData as $secteurId => $annees) {
                foreach ($annees as $annee => $data) {

                    if (empty($data['valeur']) || empty($data['annee'])) {
                        $errors[] = "Données manquantes pour le secteur $secteurId, année $annee";
                        continue;
                    }

                    $cible->valeur = sanitize_input($data['valeur']);
                    $cible->annee = sanitize_input($annee);
                    $cible->secteur_id = sanitize_input($secteurId);
                    $cible->cmr_id = $cmr_id;
                    $cible->projet_id = $projet_id;
                    $cible->add_by = $add_by;

                    if ($cible->create()) {
                        $successCount++;
                    } else {
                        $errors[] = "Erreur lors de la création de la cible pour le secteur $secteurId, année $annee";
                    }
                }
            }

            if (empty($errors)) {
                echo json_encode(['status' => 'success', 'message' => "Toutes les cibles ($successCount) ont été créées avec succès", 'count' => $successCount]);
            } else {
                echo json_encode(['status' => 'success', 'message' => "$successCount cibles créées, mais avec certaines erreurs", 'count' => $successCount, 'errors' => $errors]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $cible->cmr_id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($cible->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Cible supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la valeur cible.'));
        }
        break;

    case 'GET':
        $cmr_id = isset($_GET['cmr_id']) ? sanitize_input($_GET['cmr_id']) : null;

        if ($cmr_id) {
            $cible->cmr_id = $cmr_id;
            $data = $cible->readByCMR();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails de la valeur cible', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Cible non trouvée'));
            }
        } else {
            $data = $cible->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des actions', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune cibble trouvée'));
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
