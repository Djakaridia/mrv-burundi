<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Niveau.php';

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
$niveau = new Niveau($db);
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
            $niveau->name = sanitize_input($_POST['name']);
            $niveau->type = sanitize_input($_POST['type']);
            $niveau->level = sanitize_input($_POST['level']);
            $niveau->programme = sanitize_input($_POST['programme']);
            $niveau->add_by = sanitize_input($payload['user_id']);

            if (empty($niveau->name) || empty($niveau->type)) {
                echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                exit();
            }

            if ($niveau->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Niveau créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du niveau.'));
            }
        } else {
            // Update
            $niveau->id = sanitize_input($_GET['id']);
            $niveau->name = sanitize_input($_POST['name']);
            $niveau->type = sanitize_input($_POST['type']);
            $niveau->level = sanitize_input($_POST['level']);
            $niveau->programme = sanitize_input($_POST['programme']);
            $niveau->add_by = sanitize_input($payload['user_id']);

            if ($niveau->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Niveau modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du niveau.'));
            }
        }
        break;

    case 'DELETE':
        $niveau->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($niveau->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Niveau supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du niveau.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $programme = isset($_GET['programme_id']) ? sanitize_input($_GET['programme_id']) : null;

        if ($id) {
            $niveau->id = $id;
            $data = $niveau->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails du niveau', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Niveau non trouvé'));
            }
        } else {
            if ($programme) {
                $niveau->programme = $programme;
                $data = $niveau->readByProgramme();
                if ($data) {
                    echo json_encode(array('status' => 'success', 'message' => 'Liste des niveaux', 'data' => $data));
                } else {
                    echo json_encode(array('status' => 'warning', 'message' => 'Aucun niveau trouvé'));
                }
            } else {
                $data = $niveau->read();
                if ($data) {
                    echo json_encode(array('status' => 'success', 'message' => 'Liste des niveaux', 'data' => $data));
                } else {
                    echo json_encode(array('status' => 'warning', 'message' => 'Aucun niveau trouvé'));
                }
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
