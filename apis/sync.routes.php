<?php
session_start();
$routePath = '../';

require_once $routePath . 'config/cors-access.php';
require_once $routePath . 'config/database.php';
require_once $routePath . 'config/functions.php';
require_once $routePath . 'models/Secteur.php';
require_once $routePath . 'models/Projet.php';
require_once $routePath . 'models/Inventory.php';
require_once $routePath . 'models/Structure.php';
require_once $routePath . 'models/Convention.php';

configureCORS();
header("Content-Type: application/json; charset=UTF-8");

// Connexion base de données
$database = new Database();
$db = $database->getConnection();

// Initialisation des modèles
$secteur = new Secteur($db);
$projet = new Projet($db);
$inventory = new Inventory($db);
$structure = new Structure($db);
$convention = new Convention($db);

// Récupération des secteurs actifs
$data_secteurs = $secteur->read();
$data_secteurs = array_filter($data_secteurs, function ($secteur) {
    return $secteur['state'] == 'actif';
});
$configSecteurs = [];
foreach ($data_secteurs as $secteur) {
    $configSecteurs[strtoupper(removeAccents($secteur['name']))] = $secteur;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $secteur = isset($_GET['sector']) ? strtoupper(removeAccents($_GET['sector'])) : null;

    if (!$secteur) {
        echo json_encode(["status" => "error", "message" => "Paramètre 'sector' manquant."]);
        exit;
    }

    if (!array_key_exists($secteur, $configSecteurs)) {
        echo json_encode(["status" => "error", "message" => "Secteur '$secteur' non reconnu."]);
        exit;
    }

    // ⚙️ Appel de l’API en ligne expo.routes.php
    // $url = $configSecteurs[$secteur]['source'];
    // $ch = curl_init();
    // curl_setopt($ch, CURLOPT_URL, $url . '?sector=' . urlencode($secteur));
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    // $response = curl_exec($ch);
    // $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch);

    // ⚙️ Appel de l’API locale expo.routes.php
    $url = __DIR__ . "/expo.routes.php";
    $localApiUrl = "http://localhost" . dirname($_SERVER['PHP_SELF']) . "/expo.routes.php?sector=" . urlencode($secteur);
    $httpCode = 200;
    $response = @file_get_contents($localApiUrl);


    if ($httpCode !== 200 || !$response) {
        echo json_encode(["status" => "error", "message" => "Erreur de connexion avec l'API du secteur $secteur."]);
        exit;
    }
    
    $result = json_decode($response, true);
    if (!$result || $result['status'] !== 'success') {
        echo json_encode(["status" => "error", "message" => "Réponse invalide ou vide du secteur $secteur."]);
        exit;
    }

    $dataStrc = $result['data'];
    $resume = [
        'inventaire_ges' => count($dataStrc['inventaire_ges']),
        'action_attenuation' => count($dataStrc['action_attenuation']),
        'action_adaptation' => count($dataStrc['action_adaptation']),
        'structures' => count($dataStrc['structures']),
        'financements' => count($dataStrc['financements']),
    ];
    $metaStrc = [
        'secteur' => $configSecteurs[$secteur]['name']??"",
        'domaine' => $configSecteurs[$secteur]['domaine']??"",
        'source' => $configSecteurs[$secteur]['source']??"",
        'date_update' => date('Y-m-d H:i:s'),
    ];

    try {
        // // --- Inventaires GES ---
        // foreach ($dataStrc['inventaire_ges'] as $inv) {
        //     $inventory->createOrUpdateFromSync($inv, $secteur);
        //     $resume['inventaire_ges']++;
        // }

        // // --- Actions Atténuation / Adaptation / Prévention ---
        // foreach ($dataStrc['action_attenuation'] as $act) {
        //     $projet->createOrUpdateFromSync($act, $secteur, 'ATTENUATION');
        //     $resume['action_attenuation']++;
        // }
        // foreach ($dataStrc['action_adaptation'] as $act) {
        //     $projet->createOrUpdateFromSync($act, $secteur, 'ADAPTATION');
        //     $resume['action_adaptation']++;
        // }

        // // --- Structures ---
        // foreach ($dataStrc['structures'] as $st) {
        //     $structure->createOrUpdateFromSync($st, $secteur);
        //     $resume['structures']++;
        // }

        // // --- Financements ---
        // foreach ($dataStrc['financements'] as $fin) {
        //     $convention->createOrUpdateFromSync($fin, $secteur);
        //     $resume['financements']++;
        // }

        echo json_encode([
            "status" => "success",
            "message" => "Synchronisation réussie",
            "data" => ["metadonnees" => $metaStrc, "donnees" => $dataStrc, "resume" => $resume]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Erreur pendant l’insertion : " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée."]);
}

$db = null;
exit();
