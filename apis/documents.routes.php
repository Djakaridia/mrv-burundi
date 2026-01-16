<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Documents.php';

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
$document = new Documents($db);
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
            if (isset($_FILES['file']) && isset($_POST['name']) && isset($_POST['dossier_id'])) {
                $fileName = $_FILES['file']['name'];
                $fileTmpName = $_FILES['file']['tmp_name'];
                $fileSize = $_FILES['file']['size'];
                $fileError = $_FILES['file']['error'];
                $fileType = $_FILES['file']['type'];
                $allow_file = strval($_POST['allow_files']);

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = uniqid('', true) . '.' . $fileExt;
                $fileDestination = $uploadDirectory . $newFileName;

                // Vérification de l'extension
                if (!in_array('.' . $fileExt, explode(', ', $allow_file))) {
                    http_response_code(400);
                    echo json_encode(array('status' => 'danger', 'message' => 'Type de fichier non autorisé. Veuillez choisir un fichier de type: ' . $allow_file));
                    exit();
                }

                // Vérification et déplacement du fichier
                if ($fileError === 0) {
                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        $document->name = sanitize_input($_POST['name']);
                        $document->file_type = $fileExt;
                        $document->file_path = $fileDestination;
                        $document->file_size = $fileSize;
                        $document->description = sanitize_input($_POST['description']);
                        $document->dossier_id = sanitize_input($_POST['dossier_id']);
                        $document->entity_id = sanitize_input($_POST['entity_id'] ?? 0);
                        $document->add_by = sanitize_input($payload['user_id']);

                        if ($document->create()) {
                            http_response_code(200);
                            echo json_encode(array('status' => 'success', 'message' => 'Document créé avec succès.', 'file_path' => $fileDestination));
                        } else {
                            unlink($fileDestination);
                            http_response_code(503);
                            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création du document.'));
                        }
                    } else {
                        http_response_code(500);
                        echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors du téléchargement du fichier.'));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array('status' => 'danger', 'message' => 'Erreur avec le fichier uploadé.'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun fichier reçu.'));
            }
        } else {
            $document->id = $id;
            $document->name = sanitize_input($_POST['name']);
            $document->description = sanitize_input($_POST['description']);
            $document->dossier_id = sanitize_input($_POST['dossier_id']);
            $document->entity_id = sanitize_input($_POST['entity_id'] ?? 0);
            $document->add_by = sanitize_input($payload['user_id']);

            if ($document->update()) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Document modifié avec succès.'));
            } else {
                http_response_code(503);
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du document.'));
            }
        }

        break;

    case 'DELETE':
        $document->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $delete_document = $document->readById();

        if ($document->delete()) {
            deleteFile($delete_document['file_path']);
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Document supprimé avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du document.'));
        }
        break;

    case 'PUT':
        $document->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        if (isset($state) && $document->updateState($state)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Document modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification du Document.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $projet_id = isset($_GET['projet_id']) ? sanitize_input($_GET['projet_id']) : null;

        if ($id) {
            $document->id = $id;
            $data = $document->readById();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Détails du document', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Document non trouvé'));
            }
        } else {
            $data = $document->read();
            if ($data) {
                http_response_code(200);
                echo json_encode(array('status' => 'success', 'message' => 'Liste des documents', 'data' => $data));
            } else {
                http_response_code(404);
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun document trouvé'));
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
