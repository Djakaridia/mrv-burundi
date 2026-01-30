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
    return trim($data);
}

switch ($requestMethod) {
    case 'POST':
        $action = isset($_GET['action']) ? sanitize_input($_GET['action']) : null;
        if ($action == 'data') {
            try {
                if (!isset($_GET['inventory']) || !is_numeric($_GET['inventory'])) {
                    echo json_encode(["status" => "error", "message" => "Inventaire invalide ou manquant"]);
                    exit();
                }

                $inventory->id = intval($_GET['inventory']);
                $data_inventory = $inventory->readById();
                if (!$data_inventory || !isset($data_inventory['viewtable'])) {
                    echo json_encode(["status" => "error", "message" => "Aucun inventaire trouvé pour l'année spécifiée"]);
                    exit();
                }

                $tableName = $data_inventory['viewtable'];
                if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $tableName)) {
                    echo json_encode(["status" => "error", "message" => "Nom de table invalide ou trop long"]);
                    exit();
                }

                if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                    $errorMessage = match ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) {
                        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "Le fichier est trop volumineux",
                        UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement téléchargé",
                        UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été téléchargé",
                        UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant",
                        UPLOAD_ERR_CANT_WRITE => "Échec de l'écriture du fichier",
                        UPLOAD_ERR_EXTENSION => "Extension PHP bloquée",
                        default => "Erreur inconnue lors du téléchargement"
                    };
                    echo json_encode(["status" => "error", "message" => $errorMessage]);
                    exit();
                }

                $allowedExtensions = ['xlsx', 'xls', 'csv', 'ods'];
                $fileExt = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                if (!in_array($fileExt, $allowedExtensions)) {
                    echo json_encode(["status" => "error", "message" => "Type de fichier non autorisé. Types acceptés : " . implode(', ', $allowedExtensions)]);
                    exit();
                }

                $maxFileSize = 10 * 1024 * 1024;
                if ($_FILES['file']['size'] > $maxFileSize) {
                    echo json_encode(["status" => "error", "message" => "Le fichier ne doit pas dépasser 10MB"]);
                    exit();
                }

                $uploadDirectory = rtrim($uploadDirectory, '/') . '/';
                if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
                    echo json_encode(["status" => "error", "message" => "Le répertoire de destination n'existe pas ou n'est pas accessible en écriture"]);
                    exit();
                }

                $newFileName = sprintf('%d-%s.%s', $data_inventory['annee'], bin2hex(random_bytes(8)), $fileExt);
                $fileDestination = $uploadDirectory . $newFileName;
                $fileDestination = realpath(dirname($fileDestination)) . '/' . basename($newFileName);
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $fileDestination)) {
                    echo json_encode(["status" => "error", "message" => "Impossible de déplacer le fichier téléchargé"]);
                    exit();
                }

                $inventory->id = $data_inventory['id'] ?? null;
                $inventory->file = $fileDestination;
                if (!$inventory->updateFile()) {
                    echo json_encode(["status" => "error", "message" => "Échec de la mise à jour du fichier dans la base de données"]);
                    exit();
                }

                try {
                    $spreadsheet = IOFactory::load($fileDestination);
                    $sheetNames = ["Inventaire", "Sheet1", "Feuil1", "Feuille1", "Inventaire GES"];
                    $sheetName = null;

                    foreach ($sheetNames as $name) {
                        if ($spreadsheet->getSheetByName($name)) {
                            $sheetName = $name;
                            break;
                        }
                    }

                    if (!$sheetName) {
                        $sheet = $spreadsheet->getActiveSheet();
                        $sheetName = $sheet->getTitle();
                    }

                    $sheet = $spreadsheet->getSheetByName($sheetName) ?? $spreadsheet->getActiveSheet();
                    $rawData = $sheet->toArray(null, true, true, true);
                    if (count($rawData) < 2) {
                        echo json_encode(["status" => "error", "message" => "Le fichier est vide ou ne contient pas suffisamment de données"]);
                        exit();
                    }

                    $headers = array_shift($rawData);
                    $columnsDefs = [];
                    $cleanHeaders = [];
                    $columnMap = [];
                    $columnTypes = [];

                    foreach ($headers as $key => $header) {
                        $header = (string)($header ?? '');
                        $cleanHeader = removeAccents(trim($header));
                        $colName = cleanColumnName($cleanHeader);
                        if ($colName === '' || $colName === 'column_') continue;

                        $baseColName = $colName;
                        $counter = 1;
                        while (in_array("`$colName`", $cleanHeaders)) {
                            $colName = $baseColName . '_' . $counter++;
                        }

                        $isNumericColumn = false;
                        $hasDecimal = false;
                        $sampleSize = min(10, count($rawData));
                        $numericCount = 0;
                        $decimalCount = 0;

                        for ($i = 0; $i < $sampleSize; $i++) {
                            if (isset($rawData[$i][$key])) {
                                $value = $rawData[$i][$key];
                                $formattedValue = formatCellValue($value);

                                if (is_numeric($formattedValue)) {
                                    $numericCount++;
                                    if (is_float($formattedValue) || (is_string($formattedValue) && strpos($formattedValue, '.') !== false)) $decimalCount++;
                                }
                            }
                        }

                        if ($numericCount / $sampleSize > 0.7) {
                            $isNumericColumn = true;
                            if ($decimalCount / max(1, $numericCount) > 0.5) {
                                $hasDecimal = true;
                            }
                        }

                        if ($isNumericColumn) {
                            if ($hasDecimal) {
                                $columnsDefs[] = "`$colName` DECIMAL(15,3) NULL";
                                $columnTypes[$key] = 'decimal';
                            } else {
                                $columnsDefs[] = "`$colName` INT NULL";
                                $columnTypes[$key] = 'int';
                            }
                        } else {
                            $columnsDefs[] = "`$colName` TEXT NULL";
                            $columnTypes[$key] = 'text';
                        }

                        $cleanHeaders[] = "`$colName`";
                        $columnMap[$key] = $colName;
                    }

                    if (empty($columnsDefs)) {
                        echo json_encode(["status" => "error", "message" => "Aucune colonne valide détectée dans le fichier Excel."]);
                        exit();
                    }

                    $columnsSql = implode(", ", $columnsDefs);

                    try {
                        $db->exec("DROP TABLE IF EXISTS `$tableName`");
                        $db->exec("CREATE TABLE `$tableName` ( id INT PRIMARY KEY AUTO_INCREMENT, $columnsSql, imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                        $db->beginTransaction();

                        $colNames = implode(",", $cleanHeaders);
                        $placeholders = rtrim(str_repeat("?,", count($cleanHeaders)), ",");
                        $insertStmt = $db->prepare("INSERT INTO `$tableName` ($colNames) VALUES ($placeholders)");
                        $validRows = 0;

                        foreach ($rawData as $rowIndex => $row) {
                            $values = [];

                            foreach (array_keys($headers) as $key) {
                                if (!isset($columnMap[$key])) continue;
                                $value = $row[$key] ?? null;
                                $formattedValue = formatCellValue($value);

                                if (isset($columnTypes[$key])) {
                                    if ($columnTypes[$key] === 'decimal' && is_numeric($formattedValue)) {
                                        $formattedValue = number_format((float)$formattedValue, 3, '.', '');
                                    } elseif ($columnTypes[$key] === 'int' && is_numeric($formattedValue)) {
                                        $formattedValue = (int)$formattedValue;
                                    }
                                }

                                $values[] = $formattedValue;
                            }

                            $hasData = false;
                            foreach ($values as $val) {
                                if ($val !== null && $val !== '') {
                                    $hasData = true;
                                    break;
                                }
                            }

                            if ($hasData) {
                                $insertStmt->execute($values);
                                $validRows++;
                            }
                        }

                        $db->commit();
                        error_log("Importation réussie dans la table $tableName : $validRows lignes importées");
                        echo json_encode(["status" => "success", "message" => "Importation réussie.", "table" => $tableName, "rows_imported" => $validRows, "columns" => count($cleanHeaders)]);
                    } catch (Exception $e) {
                        echo json_encode(["status" => "danger", "message" => "Erreur lors du traitement du fichier : " . $e->getMessage()]);
                    }
                } catch (Exception $e) {
                    if (file_exists($fileDestination)) unlink($fileDestination);
                    echo json_encode(["status" => "danger", "message" => "Erreur lors du traitement du fichier : " . $e->getMessage()]);
                }
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "danger", "message" => $e->getMessage(), "code" => $e->getCode()]);
                exit();
            }
        } else {
            $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
            if (!$id) {
                $inventory->name = sanitize_input($_POST['name']);
                $inventory->annee = sanitize_input($_POST['annee']);
                $inventory->unite = sanitize_input($_POST['unite']);
                $inventory->methode_ipcc = sanitize_input($_POST['methode_ipcc']);
                $inventory->source_donnees = sanitize_input($_POST['source_donnees']);
                $inventory->description = sanitize_input($_POST['description']);
                $inventory->viewtable = "vw_" . uniqid();
                $inventory->afficher = "non";
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
                $inventory->unite = sanitize_input($_POST['unite']);
                $inventory->methode_ipcc = sanitize_input($_POST['methode_ipcc']);
                $inventory->source_donnees = sanitize_input($_POST['source_donnees']);
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

    case 'PUT':
        $inventory->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $afficher = sanitize_input($_GET['state']);

        if ($inventory->updateAffichage($afficher)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Inventaire modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'inventaire.'));
        }
        break;
    default:
        echo json_encode(array("message" => "Method not allowed."));
        break;
}

$db = null;
exit();
