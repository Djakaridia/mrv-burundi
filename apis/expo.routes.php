<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/Projet.php';
require_once $routePath . 'models/Inventory.php';
require_once $routePath . 'models/Structure.php';
require_once $routePath . 'models/Convention.php';

loadVarEnv();
configureCORS();
header("Content-Type: application/json; charset=UTF-8");

// Connexion base de données
$database = new Database();
$db = $database->getConnection();

// Initialisation des modèles
$projet = new Projet($db);
$inventory = new Inventory($db);
$structure = new Structure($db);
$convention = new Convention($db);

$dataStruc = [
    "inventaire_ges" => [],
    "action_attenuation" => [],
    "action_adaptation" => [],
    "structures" => [],
    "financements" => [],
];

// ==========================================================
// ROUTE GET : récupération des données sectorielles
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $secteur = isset($_GET['sector']) ? strtoupper(removeAccents($_GET['sector'])) : null;

    try {
        // -------------------- Projets (Actions) ------------------------
        $resultProjets = $projet->read();
        if ($resultProjets) {
            $dataStruc['action_attenuation'] = array_values(array_filter($resultProjets, fn($p) => strtoupper(removeAccents($p['action_name'] ?? "")) === 'ATTENUATION'));
            $dataStruc['action_adaptation'] = array_values(array_filter($resultProjets, fn($p) => strtoupper(removeAccents($p['action_name'] ?? "")) === 'ADAPTATION'));
        }

        // -------------------- Inventaires ------------------------
        $resultInv = $inventory->read();
        if ($resultInv) {
            $inventory_data = [];
            foreach ($resultInv as $row) {
                $view_data = json_decode($inventory->readData($row['viewtable']), true);
                $inventory_data[] = array_merge($row, ["viewdata" => $view_data ?? []]);
            }
            $dataStruc['inventaire_ges'] = $inventory_data;
        }

        // -------------------- Structures ------------------------
        $resultStruc = $structure->read();
        if ($resultStruc) {
            $dataStruc['structures'] = $resultStruc;
        }

        // -------------------- Financements ------------------------
        $resultFinancements = $convention->read();
        if ($resultFinancements) {
            $dataStruc['financements'] = $resultFinancements;
        }

        echo json_encode(['status' => 'success', 'message' => "Synchronisation terminée", 'data' => $dataStruc], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur interne : ' . $e->getMessage()]);
    }
} else {
    echo json_encode([
        'status' => 'danger',
        'message' => 'Méthode non autorisée.'
    ]);
}

// Fermeture propre
$db = null;
exit();
