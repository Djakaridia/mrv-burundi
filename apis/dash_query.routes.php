<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/RequeteFiche.php';

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
$requete = new RequeteFiche($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'POST':
        $table = sanitize_input($_POST['feuille']);
        $operator = sanitize_input($_POST['operator']);
        $colValue = sanitize_input($_POST['col_value']);
        $colGroup = sanitize_input($_POST['col_group']);
        $champ_criteres = sanitize_input($_POST['champ_criteres']);
        $condition_criteres = sanitize_input($_POST['condition_criteres']);
        $valeur_criteres = sanitize_input($_POST['valeur_criteres']);
        $et_ou_criteres = sanitize_input($_POST['et_ou_criteres']);
        $requete->name = sanitize_input($_POST['intitule']);
        $requete->projet_id = (int)sanitize_input($_POST['projet_id'] ?? 0);
        $requete->indicateur_id = (int)sanitize_input($_POST['indicateur_id'] ?? 0);
        $requete->query = $sql;
        $requete->add_by = $payload['id'];

        if ($requete->create()) {
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Requête créée avec succès']);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'danger', 'message' => 'Erreur lors de la création de la requête']);
        }
        break;

    case 'DELETE':

        break;

    case 'GET':

        break;

    default:
        echo json_encode(array("message" => "Method not allowed."));
        break;
}

// Close database connection
$db = null;
exit();
