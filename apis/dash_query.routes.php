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
        $table = $_POST['feuille'];
        $operator = $_POST['operator'];
        $colValue = $_POST['col_value'];
        $colGroup = $_POST['col_group'];
        $champ_criteres = $_POST['champ_criteres'];
        $condition_criteres = $_POST['condition_criteres'];
        $valeur_criteres = $_POST['valeur_criteres'];
        $et_ou_criteres = $_POST['et_ou_criteres'];

        $requete->name = $_POST['intitule'];
        $requete->projet_id = $_POST['projet_id'];
        $requete->cmr_id = null;
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
