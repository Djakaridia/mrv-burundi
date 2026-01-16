<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/NiveauIndicateur.php';

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
$niveauIndic = new NiveauIndicateur($db);
$requestMethod = $_SERVER['REQUEST_METHOD'];

function sanitize_input($data)
{
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return trim($data);
}

function validateRequiredFields($fields, $data)
{
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return "Le champ $field est requis";
        }
    }
    return null;
}

switch ($requestMethod) {
    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if (!$id) {
            try {
                if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
                    $input = json_decode(file_get_contents('php://input'), true);
                } else {
                    $input = $_POST;
                }

                $requiredFields = ['type', 'intitule', 'unite'];
                $error = validateRequiredFields($requiredFields, $input);
                if ($error) {
                    echo json_encode(['status' => 'danger', 'message' => $error]);
                    exit();
                }

                $niveauIndic->type = sanitize_input($input['type']);
                $niveauIndic->intitule = sanitize_input($input['intitule']);
                $niveauIndic->unite = sanitize_input($input['unite']);
                $niveauIndic->resultat = sanitize_input($input['resultat']);
                $niveauIndic->add_by = $payload['user_id'];

                if (isset($input['cibles']) && !empty($input['cibles'])) {
                    if (is_string($input['cibles']) && !empty($input['cibles'])) {
                        json_decode($input['cibles']);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $niveauIndic->cibles = $input['cibles'];
                        } else {
                            $niveauIndic->cibles = sanitize_input($input['cibles']);
                        }
                    } elseif (is_array($input['cibles'])) {
                        $niveauIndic->cibles = json_encode($input['cibles']);
                    }
                }

                if ($niveauIndic->create()) {
                    echo json_encode(['status' => 'success','message' => 'Indicateur créé avec succès','data' => ['id' => $niveauIndic->id,'type' => $niveauIndic->type,'intitule' => $niveauIndic->intitule]]);
                } else {
                    echo json_encode(['status' => 'danger', 'message' => 'Erreur lors de la création de l\'indicateur']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                if (!$input) {
                    $input = $_POST;
                }

                $niveauIndic->id = $id;
                if (!$niveauIndic->readById()) {
                    echo json_encode(['status' => 'danger', 'message' => 'Indicateur non trouvé']);
                    exit();
                }

                if (isset($input['type'])) {
                    $niveauIndic->type = sanitize_input($input['type']);
                }

                if (isset($input['resultat'])) {
                    $niveauIndic->resultat = sanitize_input($input['resultat']);
                }

                if (isset($input['intitule'])) {
                    $niveauIndic->intitule = sanitize_input($input['intitule']);
                }

                if (isset($input['unite'])) {
                    $niveauIndic->unite = sanitize_input($input['unite']);
                }

                if (isset($input['cibles'])) {
                    if (is_array($input['cibles'])) {
                        $niveauIndic->cibles = json_encode($input['cibles']);
                    } elseif (!empty($input['cibles'])) {
                        $niveauIndic->cibles = sanitize_input($input['cibles']);
                    } else {
                        $niveauIndic->cibles = null;
                    }
                }

                $niveauIndic->updated_at = date('Y-m-d H:i:s');

                if ($niveauIndic->update()) {
                    echo json_encode(['status' => 'success','message' => 'Indicateur mis à jour avec succès','data' => ['id' => $niveauIndic->id,'type' => $niveauIndic->type,'intitule' => $niveauIndic->intitule]]);
                } else {
                    echo json_encode(['status' => 'danger','message' => 'Erreur lors de la mise à jour de l\'indicateur']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }

        break;

    case 'DELETE':
        try {
            $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
            if (!$id) {
                echo json_encode(['status' => 'danger', 'message' => 'ID manquant']);
                exit();
            }

            $niveauIndic->id = $id;
            if ($niveauIndic->delete()) {
                echo json_encode(['status' => 'success', 'message' => 'Indicateur supprimé avec succès']);
            } else {
                echo json_encode(['status' => 'danger', 'message' => 'Erreur lors de la suppression de l\'indicateur']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'GET':
        try {
            $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
            $resultatId = isset($_GET['resultat']) ? sanitize_input($_GET['resultat']) : null;

            if ($id) {
                $niveauIndic->id = $id;
                $data = $niveauIndic->readById();

                if ($data) {
                    echo json_encode(['status' => 'success', 'message' => 'Détails de l\'indicateur', 'data' => $data]);
                } else {
                    echo json_encode(['status' => 'warning', 'message' => 'Indicateur non trouvé']);
                }
            }
            elseif($resultatId){
                $niveauIndic->resultat = $resultatId;
                $data = $niveauIndic->readByResultat();

                if ($data) {
                    echo json_encode(['status' => 'success', 'message' => 'Liste des indicateurs', 'data' => $data]);
                } else {
                    echo json_encode(['status' => 'warning', 'message' => 'Indicateur non trouvé']);
                }   
            }
             else {
                $data = $niveauIndic->read();

                if ($data) {
                    echo json_encode(['status' => 'success','message' => 'Liste des indicateurs','data' => $data]);
                } else {
                    echo json_encode(['status' => 'warning','message' => 'Aucun indicateur trouvé','data' => []]);
                }
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
        break;
}

// Close database connection
$db = null;
exit();
