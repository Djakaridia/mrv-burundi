<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Notification.php';

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
$notification = new Notification($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $notification->id = $id;
            $result = $notification->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de la notification', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune notification trouvée avec cet identifiant.'));
            }
        } else {
            $result = $notification->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des notifications', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucune notification trouvée.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        $action = isset($_GET['action']) ? sanitize_input($_GET['action']) : null;

        if ($id) {
            if ($action === 'read') {
                $notification->id = $id;
                if ($notification->markAsRead()) {
                    echo json_encode(array('status' => 'success', 'message' => 'Notification marquée comme lue.'));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la marquage de la notification comme lue.'));
                }
            } elseif ($action === 'star') {
                $notification->id = $id;
                if ($notification->markAsStarred(1)) {
                    echo json_encode(array('status' => 'success', 'message' => 'Notification marquée comme favori.'));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la marquage de la notification comme favori.'));
                }
            } elseif ($action === 'unstar') {
                $notification->id = $id;
                if ($notification->markAsStarred(0)) {
                    echo json_encode(array('status' => 'success', 'message' => 'Notification marquée comme non favori.'));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la marquage de la notification comme non favori.'));
                }
            } elseif ($action === 'archive') {
                $notification->id = $id;
                if ($notification->markAsArchived(1)) {
                    echo json_encode(array('status' => 'success', 'message' => 'Notification archivée.'));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de l\'archivage de la notification.'));
                }
            } elseif ($action === 'unarchive') {
                $notification->id = $id;
                if ($notification->markAsArchived(0)) {
                    echo json_encode(array('status' => 'success', 'message' => 'Notification déarchivée.'));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la déarchivage de la notification.'));
                }
            }

            // $notification->id = $id;
            // $notification->titre = sanitize_input($_POST['titre']);
            // $notification->message = $_POST['message'];
            // $notification->type = sanitize_input($_POST['type']);
            // $notification->entity_type = sanitize_input($_POST['entity_type'] ?? "");
            // $notification->entity_id = sanitize_input($_POST['entity_id'] ?? "");
            // $notification->user_id = sanitize_input($_POST['user_id'] ?? "");
            // $notification->add_by = sanitize_input($payload['user_id']);

            // if (empty($notification->titre) || empty($notification->message)) {
            //     echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
            //     exit();
            // }

            // if ($notification->update()) {
            //     echo json_encode(array('status' => 'success', 'message' => 'Notification modifiée avec succès.'));
            // } else {
            //     echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de la notification.'));
            // }
        } else {
            $notification->titre = sanitize_input($_POST['titre']);
            $notification->message = $_POST['message'];
            $notification->type = sanitize_input($_POST['type']);
            $notification->entity_type = sanitize_input($_POST['entity_type'] ?? "");
            $notification->entity_id = sanitize_input($_POST['entity_id'] ?? "");
            $notification->user_id = sanitize_input($_POST['user_id'] ?? "");
            $notification->add_by = sanitize_input($payload['user_id']);

            if (empty($notification->titre) || empty($notification->message)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($notification->create()) {
                echo json_encode(array('status' => 'success', 'message' => 'Notification créée avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de la notification.'));
            }
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $notification->id = $id;

        if ($notification->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Notification supprimée avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de la notification.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();