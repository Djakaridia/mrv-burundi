<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/jwt-token.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/Indicateur.php';
require_once $routePath . 'models/Projet.php';
require_once $routePath . 'models/Structure.php';
require_once $routePath . 'services/projet.mailer.php';

configureCORS();
loadVarEnv();
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
$indicateur = new Indicateur($db);
$sendmailer = new ProjetMailer($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_USERNAME'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Input sanitization function
function sanitize_input($data)
{
    return trim($data);
}

switch ($requestMethod) {
    case 'GET':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if ($id) {
            $indicateur->id = $id;
            $result = $indicateur->readById();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données de l\'indicateur', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun indicateur trouvé avec cet identifiant.'));
            }
        } else {
            $result = $indicateur->read();
            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Données des indicateurs', 'data' => $result));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Aucun indicateur trouvé.'));
            }
        }
        break;

    case 'POST':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;

        if ($id) {
            $indicateur->id = $id;
            $indicateur->code = sanitize_input($_POST['code']);
            $indicateur->intitule = sanitize_input($_POST['intitule']);
            $indicateur->description = sanitize_input($_POST['description']);
            $indicateur->unite = sanitize_input($_POST['unite']);
            $indicateur->mode_calcul = sanitize_input($_POST['mode_calcul']);
            $indicateur->responsable = sanitize_input($_POST['responsable']);
            $indicateur->annee_reference = sanitize_input($_POST['annee_reference']);
            $indicateur->valeur_reference = sanitize_input($_POST['valeur_reference']);
            $indicateur->valeur_cible = sanitize_input($_POST['valeur_cible']);
            $indicateur->latitude = sanitize_input($_POST['latitude']);
            $indicateur->longitude = sanitize_input($_POST['longitude']);
            $indicateur->referentiel_id = sanitize_input($_POST['referentiel_id']);
            $indicateur->resultat_id = sanitize_input($_POST['resultat_id']);
            $indicateur->projet_id = sanitize_input($_POST['projet_id']);
            $indicateur->add_by = sanitize_input($payload['user_id']);

            if ($indicateur->update()) {
                echo json_encode(array('status' => 'success', 'message' => 'Indicateur modifié avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'indicateur.'));
            }
        } else {
            $indicateur->code = sanitize_input($_POST['code']);
            $indicateur->intitule = sanitize_input($_POST['intitule']);
            $indicateur->description = sanitize_input($_POST['description']);
            $indicateur->unite = sanitize_input($_POST['unite']);
            $indicateur->mode_calcul = sanitize_input($_POST['mode_calcul']);
            $indicateur->responsable = sanitize_input($_POST['responsable']);
            $indicateur->annee_reference = sanitize_input($_POST['annee_reference']);
            $indicateur->valeur_reference = sanitize_input($_POST['valeur_reference']);
            $indicateur->valeur_cible = sanitize_input($_POST['valeur_cible']);
            $indicateur->latitude = sanitize_input($_POST['latitude']);
            $indicateur->longitude = sanitize_input($_POST['longitude']);
            $indicateur->referentiel_id = sanitize_input($_POST['referentiel_id']);
            $indicateur->resultat_id = sanitize_input($_POST['resultat_id']);
            $indicateur->projet_id = sanitize_input($_POST['projet_id']);
            $indicateur->add_by = sanitize_input($payload['user_id']);

            // Get projet data
            $projet = new Projet($db);
            $projet->id = $indicateur->projet_id;
            $projet_data = $projet->readById();

            // Get structure data
            $structure = new Structure($db);
            $structure->id = $projet_data['structure_id'];
            $structure_data = $structure->readById();

            if (empty($indicateur->code) || empty($indicateur->intitule) || empty($indicateur->referentiel_id) || empty($indicateur->resultat_id) || empty($indicateur->projet_id)) {
                echo json_encode(array('status' => 'warning', 'message' => 'Veuillez remplir tous les champs !!!'));
                exit();
            }

            if ($indicateur->create()) {
                $sendmailer->sendAddIndicateur($structure_data['email'], $structure_data['sigle'], $indicateur->intitule, $projet_data['name'], $projet_data['id']);
                echo json_encode(array('status' => 'success', 'message' => 'Indicateur créé avec succès.'));
            } else {
                echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la création de l\'indicateur.'));
            }
        }
        break;

    case 'PUT':
        $indicateur->id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $state = sanitize_input($_GET['state']);

        if (isset($state) && $indicateur->updateState($state)) {
            http_response_code(200);
            echo json_encode(array('status' => 'success', 'message' => 'Indicateur modifié avec succès.'));
        } else {
            http_response_code(503);
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la modification de l\'Indicateur.'));
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : die(json_encode(['error' => 'ID manquant']));
        $indicateur->id = $id;

        if ($indicateur->delete()) {
            echo json_encode(array('status' => 'success', 'message' => 'Indicateur supprimé avec succès.'));
        } else {
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur lors de la suppression de l\'indicateur.'));
        }
        break;

    default:
        echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
        break;
}

// Close database connection
$db = null;
exit();