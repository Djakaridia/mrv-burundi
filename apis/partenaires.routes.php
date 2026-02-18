<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/Partenaire.php';

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

$database = new Database();
$db = $database->getConnection();
$partenaire = new Partenaire($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uploadDirectory = $routePath . 'uploads/';

function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id === null) {
            $partenaire->code = sanitize_input($_POST['code']);
            $partenaire->sigle = sanitize_input($_POST['sigle']);
            $partenaire->email = sanitize_input($_POST['email']);
            $partenaire->description = sanitize_input($_POST['description']??"");
            $partenaire->perimetre = sanitize_input($_POST['perimetre']);
            $partenaire->add_by = sanitize_input($payload['user_id']);

            if (empty($partenaire->code) || empty($partenaire->sigle) || empty($partenaire->email)) {
                die(json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs.')));
            }

            if ($partenaire->create()) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Acteur créé avec succès.'));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de l\'acteur.'));
            }
        } else {
            $partenaire->id = $id;
            $partenaire->code = sanitize_input($_POST['code']);
            $partenaire->sigle = sanitize_input($_POST['sigle']);
            $partenaire->email = sanitize_input($_POST['email']);
            $partenaire->description = sanitize_input($_POST['description']??"");
            $partenaire->perimetre = sanitize_input($_POST['perimetre']);
            $partenaire->add_by = sanitize_input($payload['user_id']);

            if ($partenaire->update()) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Acteur modifié avec succès.'));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'acteur.'));
            }
        }
        break;

    case 'DELETE':
        $partenaire->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $partenaireData = $partenaire->readById();

        if ($partenaire->delete()) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Acteur supprimé avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de l\'acteur.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $partenaire->id = $id;
            $data = $partenaire->readById();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Données de l\'acteur', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucune donnée trouvée pour l\'ID ' . $id));
            }
        } else {
            $data = $partenaire->read();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Liste des acteurs', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun acteur trouvée'));
            }
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