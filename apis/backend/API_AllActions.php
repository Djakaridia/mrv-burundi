<?php
$routePath = '../../';

require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Mesure.php';
require_once $routePath . 'config/functions.php';


header("Content-Type: application/json");

$database = new Database();
$db = $database->getConnection();
$mesure = new Mesure($db);

$data = $mesure->readAll();

if (!empty($data)) {
    $result = [];
    foreach ($data as $row) {
        $id = $row['id'];

        if (!isset($result[$id])) {
            $result[$id] = [
                'id' => $id,
                'code' => $row['code'],
                'name' => $row['name'],
                'secteur' => $row['secteur_name'],
                'structure' => $row['structure_sigle'],
                'action_type' => $row['action_type'],
                'instrument' => $row['instrument'],
                'status' => $row['status'],
                'gaz' => $row['gaz'],
                'annee_debut' => $row['annee_debut'],
                'annee_fin' => $row['annee_fin'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude'],
                'description' => $row['description'],
                'objectif' => $row['objectif'],
                'emissions_par_annee' => [],
                'emission_totale' => 0
            ];
        }

        if ($row['annee']) {
            $result[$id]['emissions_par_annee'][$row['annee']] = $row['emission_evitee'];
            $result[$id]['emission_totale'] += $row['emission_evitee'];
        }
    }

    $mesures = array_values($result);
    echo json_encode([
        'status' => 'success',
        'message' => 'Liste des actions',
        'data' => $mesures
    ]);
} else {
    echo json_encode([
        'status' => 'warning',
        'message' => 'Aucune action trouvée'
    ]);
}

$db = null;
exit;
