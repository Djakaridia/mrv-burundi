<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Facteur.php';

configureCORS();
header("Content-Type: application/json");

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
$facteur = new FacteurEmission($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize($data)
{
    return trim($data ?? '');
}

switch ($requestMethod) {

    case 'GET':
        $id = sanitize($_GET['id'] ?? null);
        $projet_id = sanitize($_GET['projet_id'] ?? null);
        $mesure_id = sanitize($_GET['mesure_id'] ?? null);
        $referentiel_id = sanitize($_GET['referentiel_id'] ?? null);

        if ($id) {
            $facteur->id = $id;
            $data = $facteur->readById();
        } elseif ($projet_id) {
            $facteur->projet_id = $projet_id;
            $data = $facteur->readByProjet();
        } elseif ($mesure_id) {
            $facteur->mesure_id = $mesure_id;
            $data = $facteur->readByMesure();
        } elseif ($referentiel_id) {
            $facteur->referentiel_id = $referentiel_id;
            $data = $facteur->readByReferentiel();
        } else {
            $data = $facteur->read();
        }

        echo json_encode(['status' => $data ? 'success' : 'danger', 'data' => $data ?: [], 'message' => $data ? 'Données trouvées' : 'Aucune donnée trouvée']);

        break;

    case 'POST':
        $id = sanitize($_GET['id'] ?? null);

        $facteur->name = sanitize($_POST['name']);
        $facteur->unite = sanitize($_POST['unite']);
        $facteur->type = sanitize($_POST['type']);
        $facteur->gaz = sanitize($_POST['gaz']);
        $facteur->valeur = sanitize($_POST['valeur']);
        $facteur->referentiel_id = (int)sanitize($_POST['referentiel_id'] ?? 0);
        $facteur->projet_id = (int)sanitize($_POST['projet_id'] ?? 0);
        $facteur->mesure_id = (int)sanitize($_POST['mesure_id'] ?? 0);
        $facteur->add_by = $payload['user_id'];

        if (empty($facteur->name) || empty($facteur->valeur)) {
            echo json_encode(['status' => 'warning', 'message' => 'Champs obligatoires manquants']);
            exit();
        }

        if ($id) {
            $facteur->id = $id;
            if ($facteur->update()) {
                echo json_encode(['status' => 'success', 'message' => 'Facteur modifié']);
            } else {
                echo json_encode(['status' => 'danger', 'message' => 'Erreur lors de la modification']);
            }
        } else {
            if ($facteur->create()) {
                echo json_encode(['status' => 'success', 'message' => 'Facteur créé']);
            } else {
                echo json_encode(['status' => 'danger', 'message' => 'Erreur lors de la création']);
            }
        }

        break;

    case 'DELETE':
        $id = sanitize($_GET['id'] ?? null);

        if (!$id) {
            echo json_encode(['status' => 'danger', 'message' => 'ID manquant']);
            exit();
        }

        $facteur->id = $id;
        if ($facteur->delete()) {
            echo json_encode(['status' => 'success', 'message' => 'Facteur supprimé']);
        } else {
            echo json_encode(['status' => 'danger', 'message' => 'Erreur suppression']);
        }

        break;

    default:
        echo json_encode(['status' => 'danger', 'message' => 'Méthode non autorisée']);
}
