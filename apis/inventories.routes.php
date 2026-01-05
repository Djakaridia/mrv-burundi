<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Inventory.php';
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
$inventory = new Inventory($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uploadDirectory = $routePath . 'uploads/inventories/';

function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

switch ($requestMethod) {
    case 'POST':
        $action = isset($_GET['action']) ? sanitize_input($_GET['action']) : null;
        if ($action == 'data') {
            $inventory->annee = isset($_GET['annee']) ? intval($_GET['annee']) : null;
            $data_inventory = $inventory->readByAnnee();
            $tableName = $data_inventory['viewtable'];

            if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $fileName = $_FILES['file']['name'];
                $fileTmpName = $_FILES['file']['tmp_name'];
                $allow_file = strval($_POST['allow_files']);

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = $data_inventory['annee'] . '-' . uniqid('', true) . '.' . $fileExt;
                $fileDestination = $uploadDirectory . $newFileName;

                if (!in_array('.' . $fileExt, explode(', ', $allow_file))) {
                    http_response_code(400);
                    echo json_encode(['status' => 'danger', 'message' => "Type de fichier non autorisé. Veuillez choisir un fichier de type: $allow_file"]);
                    exit();
                }

                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    $inventory->id = $data_inventory['id'];
                    $inventory->name = $data_inventory['name'];
                    $inventory->annee = $data_inventory['annee'];
                    $inventory->description = $data_inventory['description'];
                    $inventory->file = $fileDestination;
                    $inventory->update();

                    try {
                        $spreadsheet = IOFactory::load($fileDestination);
                        if ($spreadsheet->getSheetByName("Inventaire GES")) {
                            $sheetName = "Inventaire GES";
                        } elseif ($spreadsheet->getSheetByName("Sheet1")) {
                            $sheetName = "Sheet1";
                        } elseif ($spreadsheet->getSheetByName("Feuil1")) {
                            $sheetName = "Feuil1";
                        } else {
                            echo json_encode(["status" => "danger", "message" => "Le fichier doit contenir une feuille nommée 'Inventaire GES', 'Sheet1' ou 'Feuil1'."]);
                            exit();
                        }

                        $sheet = $spreadsheet->getSheetByName($sheetName);
                        $data = $sheet->toArray(null, true, true, true);
                        if (count($data) < 2) {
                            echo json_encode(["status" => "danger", "message" => "Le fichier est vide ou ne contient pas suffisamment de données."]);
                            exit();
                        }

                        $headers = array_shift($data);
                        $columnsDefs = [];
                        $cleanHeaders = [];
                        foreach ($headers as $header) {
                            $colHeader = removeAccents($header);
                            $colName = cleanColumnName($colHeader);
                            $columnsDefs[] = "\"$colName\" TEXT NULL";
                            $cleanHeaders[] = "\"$colName\"";
                        }

                        $columnsSql = implode(", ", $columnsDefs);
                        try {
                            $stmt = $db->prepare("CREATE TABLE IF NOT EXISTS \"$tableName\" ($columnsSql);");
                            $stmt->execute();
                        } catch (\Throwable $th) {
                            echo json_encode(["status" => "danger", "message" => "Erreur lors de la création de la table: " . $th->getMessage()]);
                            exit();
                        }

                        try {
                            $stmt = $db->prepare("SELECT * FROM \"$tableName\" LIMIT 1");
                            $stmt->execute();
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (isset($result)) {
                                $stmt = $db->prepare("DELETE FROM \"$tableName\"");
                                $stmt->execute();
                            }else {
                                echo json_encode(["status" => "danger", "message" => "La table \"$tableName\" n'existe pas."]);
                                exit();
                            }
                        } catch (\Throwable $th) {
                            echo json_encode(["status" => "danger", "message" => "Erreur lors de la vérification ou du vidage de la table: " . $th->getMessage()]);
                            exit();
                        }

                        $colNames = implode(",", $cleanHeaders);
                        $placeholders = rtrim(str_repeat("?,", count($cleanHeaders)), ",");
                        $stmt = $db->prepare("INSERT INTO \"$tableName\" ($colNames) VALUES ($placeholders)");

                        foreach ($data as $row) {
                            $values = [];
                            foreach (array_keys($headers) as $key) {
                                $values[] = $row[$key] ?? null;
                            }
                            $stmt->execute($values);
                        }

                        echo json_encode(["status" => "success", "message" => "Importation réussie.", "table" => $tableName]);
                    } catch (Exception $e) {
                        echo json_encode(["status" => "danger", "message" => $e->getMessage()]);
                    }
                }
            }
        } else {
            $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
            if (!$id) {
                $inventory->name = sanitize_input($_POST['name']);
                $inventory->annee = sanitize_input($_POST['annee']);
                $inventory->description = sanitize_input($_POST['description']);
                $inventory->viewtable = "vw_" . uniqid();
                $inventory->add_by = sanitize_input($payload['user_id']);

                if (empty($inventory->annee) || empty($inventory->viewtable)) {
                    echo json_encode(array('status' => 'danger', 'message' => 'Veuillez remplir tous les champs obligatoires.'));
                    exit();
                }

                if ($inventory->create()) {
                    echo json_encode(array('status' => 'success', 'message' => 'Inventaire créé avec succès.'));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de l\'action.'));
                }
            } else {
                $inventory->id = sanitize_input($_GET['id']);
                $inventory->name = sanitize_input($_POST['name']);
                $inventory->annee = sanitize_input($_POST['annee']);
                $inventory->description = sanitize_input($_POST['description']);
                $inventory->add_by = sanitize_input($payload['user_id']);

                if ($inventory->update()) {
                    echo json_encode(array('status' => 'success', 'message' => 'Inventaire modifié avec succès.'));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'action.'));
                }
            }
        }
        break;

    case 'DELETE':
        $inventory->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $data_inventory = $inventory->readById();
        $viewtable = $data_inventory['viewtable'];

        if ($inventory->delete()) {
            deleteFile($data_inventory['file']);

            if ($inventory->deleteData($viewtable)) {
                echo json_encode(array('status' => 'success', 'message' => 'Inventaire supprimé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de l\'action.'));
            }
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de l\'action.'));
        }
        break;

    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $inventory->id = $id;
            $data = $inventory->readById();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Détails de l\'inventaire', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Inventaire non trouvée'));
            }
        } else {
            $data = $inventory->read();
            if ($data) {
                echo json_encode(array('status' => 'success', 'message' => 'Liste des inventaires', 'data' => $data));
            } else {
                echo json_encode(array('status' => 'warning', 'message' => 'Aucun inventaire trouvée'));
            }
        }
        break;

    default:
        echo json_encode(array("message" => "Method not allowed."));
        break;
}


function cleanColumnName($header)
{
    $header = str_replace(
        ['₀', '₁', '₂', '₃', '₄', '₅', '₆', '₇', '₈', '₉', '⁰', '¹', '²', '³', '⁴', '⁵', '⁶', '⁷', '⁸', '⁹'],
        ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
        $header
    );
    $header = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $header);
    $header = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($header));
    $header = preg_replace('/_+/', '_', $header);
    $header = trim($header, '_');
    if ($header === '') {
        $header = 'col_' . uniqid();
    }
    return $header;
}


$db = null;
exit();
