<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Projet.php';
require_once $routePath . 'models/Secteur.php';
require_once $routePath . 'models/GroupeTravail.php';
require_once $routePath . 'models/Inventory.php';

configureCORS();
header("Content-Type: application/json");

// Create a database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$projet = new Projet($db);
$secteur = new Secteur($db);
$groupeTravail = new GroupeTravail($db);
$inventory = new Inventory($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $param = isset($_GET['param']) ? htmlspecialchars(strip_tags(trim($_GET['param']))) : null;
    switch ($param) {

        //============================================================>
        // Récupération des projets
        case 'projets':
            $id = isset($_GET['id']) ? htmlspecialchars(strip_tags(trim($_GET['id']))) : null;
            if ($id) {
                $projet->id = $id;
                $result = $projet->readById();

                if ($result) {
                    $result['secteurs'] = getSecteurs($result['secteur_id'] ?? "");
                    $result['groupes'] = getGroupes(explode(',', str_replace('"', '', $result['groupes'] ?? "")));

                    echo json_encode(array('status' => 'success', 'message' => 'Données du projet', 'data' => $result));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Aucun projet trouvé avec cet identifiant.'));
                }
            } else {
                $result = $projet->read();
                foreach ($result as $key => $value) {
                    $result[$key]['secteurs'] = getSecteurs($value['secteur_id'] ?? "");
                    $result[$key]['groupes'] = getGroupes(explode(',', str_replace('"', '', $value['groupes'] ?? "")));
                }

                if ($result) {
                    echo json_encode(array('status' => 'success', 'message' => 'Données des projets', 'data' => $result));
                } else {
                    echo json_encode(array('status' => 'danger', 'message' => 'Aucun projet trouvé.'));
                }
            }
            break;

        //============================================================>
        // Récupération des inventaires
        case 'inventaires':
            $annee = isset($_GET['annee']) ? htmlspecialchars(strip_tags(trim($_GET['annee']))) : null;
            if ($annee) {
                $inventory->annee = $annee;
                $result = $inventory->readByAnnee();

                if ($result) {
                    $inventory_view_data = json_decode($inventory->readData($result['viewtable']), true);
                    $inventory_data = array_merge($result, $inventory_view_data);

                    echo json_encode(array('status' => 'success', 'message' => 'Détails de l\'inventaire', 'data' => $inventory_data));
                } else {
                    echo json_encode(array('status' => 'warning', 'message' => 'Inventaire non trouvée'));
                }
            } else {
                $result = $inventory->read();

                if ($result) {
                    $inventory_data = [];
                    foreach ($result as $key => $value) {
                        $inventory_view_data = json_decode($inventory->readData($value['viewtable']), true);
                        $inventory_data[$key] = array_merge($value, array("viewdata" => $inventory_view_data ?? []));
                    }

                    echo json_encode(array('status' => 'success', 'message' => 'Liste des inventaires', 'data' => $inventory_data));
                } else {
                    echo json_encode(array('status' => 'warning', 'message' => 'Aucun inventaire trouvée'));
                }
            }
            break;

        //============================================================>
        default:
            echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Action non autorisée.'));
    }
} else {
    echo json_encode(array('status' => 'danger', 'message' => 'Erreur !!! Requete non autorisée.'));
}





function getSecteurs($secteurs_ids)
{
    global $secteur;
    $secteurs = [];
    foreach ($secteurs_ids as $secteur_id) {
        $secteur->id = $secteur_id;
        $data = $secteur->readById();
        $secteurs[] = $data['name'] ?? "";
    }
    return $secteurs;
}

function getGroupes($groupes_ids)
{
    global $groupeTravail;
    $groupes = [];
    foreach ($groupes_ids as $groupe_id) {
        $groupeTravail->id = $groupe_id;
        $data = $groupeTravail->readById();
        $groupes[] = $data['name'] ?? "";
    }
    return $groupes;
}

// Close database connection
$db = null;
exit();
