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
    $secteurs = [];

    foreach ($data as $row) {
        $secteur = $row['secteur_name'] ?? 'Non défini';
        $status  = strtolower($row['status'] ?? '');

        if (!isset($secteurs[$secteur])) {
            $secteurs[$secteur] = [
                'secteur' => $secteur,
                'total_actions' => 0,
                'total_realises' => 0,
                'taux_realisation' => 0
            ];
        }

        $secteurs[$secteur]['total_actions']++;
        if ($status === 'realise') {
            $secteurs[$secteur]['total_realises']++;
        }
    }

    foreach ($secteurs as &$sec) {
        if ($sec['total_actions'] > 0) {
            $sec['taux_realisation'] = round(($sec['total_realises'] / $sec['total_actions']) * 100);
        } else {
            $sec['taux_realisation'] = 0;
        }
    }
    unset($sec);
    $total_actions = 0;
    $total_realises = 0;

    foreach ($secteurs as $sec) {
        $total_actions += $sec['total_actions'];
        $total_realises += $sec['total_realises'];
    }

    $taux_global = $total_actions > 0 ? round(($total_realises / $total_actions) * 100) : 0;
    $secteurs[] = [
        'secteur' => 'Total / moyenne',
        'total_actions' => $total_actions,
        'total_realises' => $total_realises,
        'taux_realisation' => $taux_global
    ];

    echo json_encode([ 'status' => 'success', 'message' => 'Statistiques par secteur', 'data' => array_values($secteurs)]);
} else {
    echo json_encode([ 'status' => 'warning', 'message' => 'Aucune action trouvée']);
}

$db = null;
exit;
