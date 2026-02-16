<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Documents.php';

configureCORS();
header("Content-Type: application/json");

function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

function jsonResponse($code, $status, $message, $data = null)
{
    http_response_code($code);
    echo json_encode(["status" => $status, "message" => $message, "data" => $data]);
    exit;
}

try {
    $jwt = JWTUtils::getBearerToken();
    $payload = JWTUtils::validateJWT($jwt);
} catch (Exception $e) {
    jsonResponse(401, "danger", "Accès non autorisé");
}

$db = (new Database())->getConnection();
$document = new Documents($db);
$uploadDir = __DIR__ . '/../uploads/';
$uploadDB = 'uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':

        if (!isset($_FILES['file'])) {
            jsonResponse(400, "danger", "Aucun fichier reçu");
        }

        $allow = $_POST['allow_files'] ?? '';
        $allowedExtensions = array_map(function ($ext) {
            return strtolower(ltrim(trim($ext), '.'));
        }, explode(',', $allow));

        $fileName = $_FILES['file']['name'];
        $fileTmp  = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileError = $_FILES['file']['error'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExtensions)) {
            jsonResponse(400, "danger", "Type de fichier non autorisé : " . $fileExt);
        }

        if ($fileError !== 0) {
            jsonResponse(400, "danger", "Erreur upload fichier");
        }

        $newName = uniqid() . '.' . $fileExt;
        $fileDestination = $uploadDir . $newName;
        $filePathDB = $uploadDB . $newName;

        if (!move_uploaded_file($fileTmp, $fileDestination)) {
            jsonResponse(500, "danger", "Impossible d’enregistrer le fichier");
        }

        $document->name = sanitize($_POST['name']);
        $document->file_type = $fileExt;
        $document->file_path = $filePathDB;
        $document->file_size = $fileSize;
        $document->description = sanitize($_POST['description']);
        $document->dossier_id = (int)sanitize($_POST['dossier_id'] ?? 0);
        $document->entity_id = !empty($_POST['entity_id']) ? (int) $_POST['entity_id'] : null;
        $document->add_by = $payload['user_id'];

        if ($document->create()) {
            jsonResponse(200, "success", "Document ajouté", $filePathDB);
        }

        unlink($fileDestination);
        jsonResponse(503, "danger", "Erreur enregistrement DB");

        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;

        if (!$id) jsonResponse(400, "danger", "ID manquant");

        $document->id = sanitize($id);
        $doc = $document->readById();

        if (!$doc) jsonResponse(404, "danger", "Document introuvable");

        if ($document->delete()) {
            $file = __DIR__ . '/../' . $doc['file_path'];
            if (file_exists($file)) unlink($file);

            jsonResponse(200, "success", "Document supprimé");
        }

        jsonResponse(503, "danger", "Erreur suppression");

        break;

    case 'GET':
        $id = $_GET['id'] ?? null;

        if ($id) {
            $document->id = sanitize($id);
            $data = $document->readById();

            if ($data) jsonResponse(200, "success", "Détails document", $data);
            jsonResponse(404, "warning", "Document non trouvé");
        }

        $data = $document->read();
        jsonResponse(200, "success", "Liste documents", $data);

        break;

    default:
        jsonResponse(405, "danger", "Méthode non autorisée");
}
