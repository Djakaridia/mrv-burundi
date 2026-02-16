<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Register.php';
require_once $routePath . 'models/Secteur.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

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
$register = new Register($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uploadDirectory = $routePath . 'uploads/registres/';

function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'POST':
        $action = $_GET['action'] ?? null;
        if ($action === 'import') {
            try {
                if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(["status" => "error", "message" => "Aucun fichier valide envoyé"]);
                    exit();
                }

                $allowedExtensions = ['xlsx', 'xls', 'csv', 'ods'];
                $fileExt = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

                if (!in_array($fileExt, $allowedExtensions)) {
                    echo json_encode(["status" => "error", "message" => "Format de fichier non autorisé"]);
                    exit();
                }

                $uploadDirectory = rtrim($uploadDirectory, '/') . '/';
                if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
                    echo json_encode(["status" => "error", "message" => "Le répertoire de destination n'existe pas ou n'est pas accessible en écriture"]);
                    exit();
                }

                $newFileName = sprintf('%d-%s.%s', date('Y'), bin2hex(random_bytes(8)), $fileExt);
                $fileDestination = $uploadDirectory . $newFileName;
                $fileDestination = realpath(dirname($fileDestination)) . DIRECTORY_SEPARATOR . basename($newFileName);


                /* ===== Lecture du fichier Excel ===== */
                $spreadsheet = IOFactory::load($_FILES['file']['tmp_name']);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, true);

                if (count($rows) < 2) {
                    echo json_encode(["status" => "error", "message" => "Le fichier ne contient aucune donnée"]);
                    exit();
                }

                /* ===== Mapping des colonnes ===== */
                $headers = array_map('trim', $rows[1]);
                $requiredHeaders = ['Code', 'Catégorie', 'Gaz', 'Emissions Année', 'Emissions Absolues', 'Niveau Emissions', 'Total Comule'];

                foreach ($requiredHeaders as $h) {
                    if (!in_array($h, $headers)) {
                        echo json_encode(["status" => "error", "message" => "Colonne manquante : $h"]);
                        exit();
                    }
                }

                $map = array_flip($headers);
                $db->beginTransaction();
                $imported = 0;

                $secteurModel = new Secteur($db);
                $data_secteurs = $secteurModel->read();

                $secteurMap = [];
                foreach ($data_secteurs as $s) {
                    if ((int)$s['parent'] === 0) $secteurMap[(string)$s['code']] = (int)$s['id'];
                }

                for ($i = 2; $i <= count($rows); $i++) {
                    $row = $rows[$i];
                    if (empty($row['A'])) continue;

                    $codeSousSecteur = trim($row[array_search('Code', $headers)]);
                    if (!preg_match('/^(\d+)/', $codeSousSecteur, $matches)) continue;

                    $codeSecteur = $matches[1];
                    if (!isset($secteurMap[$codeSecteur])) continue;

                    $register = new Register($db);
                    $register->secteur_id = $secteurMap[$codeSecteur];
                    $register->code = $codeSousSecteur;
                    $register->categorie          = trim($row[array_search('Catégorie', $headers)]);
                    $register->gaz                = trim($row[array_search('Gaz', $headers)]);
                    $register->emission_annee     = floatval($row[array_search('Emissions Année', $headers)]);
                    $register->emission_absolue   = floatval($row[array_search('Emissions Absolues', $headers)]);
                    $register->emission_niveau    = floatval($row[array_search('Niveau Emissions', $headers)]);
                    $register->emission_cumulee   = floatval($row[array_search('Total Comule', $headers)]);
                    $register->file             = $fileDestination;
                    $register->annee            = $_POST['annee'];
                    $register->inventaire_id    = (int)sanitize_input($_POST['inventaire_id'] ?? 0);
                    $register->add_by           = $payload['user_id'];

                    if (!$register->create()) {
                        echo json_encode(["status"  => "danger", "message" => "Erreur insertion ligne $i"]);
                        exit;
                    }

                    $imported++;
                }

                if (!move_uploaded_file($_FILES['file']['tmp_name'], $fileDestination)) {
                    echo json_encode(["status" => "error", "message" => "Impossible de déplacer le fichier téléchargé"]);
                    exit();
                }

                $db->commit();
                echo json_encode(["status"  => "success", "message" => "Import terminé avec succès", "rows"    => $imported]);
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();

                http_response_code(400);
                echo json_encode(["status"  => "danger", "message" => $e->getMessage()]);
            }
            break;
        }
        if ($action === 'create') {
            // TODO: Implement create action
            break;
        }

        break;

    case 'DELETE':
        $register->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));

        if ($register->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Register supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression du register.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $register->id = $id;
            $data = $register->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails du register', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Register non trouvé'));
            }
        } else {
            $data = $register->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des registers', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun register trouvé'));
            }
        }
        break;

    default:
        echo json_encode(array("message" => "Method not allowed."));
        break;
}

$db = null;
exit();
