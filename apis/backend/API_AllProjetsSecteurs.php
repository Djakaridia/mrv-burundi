<?php
$routePath = '../../';

require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Projet.php';
require_once $routePath . 'config/functions.php';

header("Content-Type: application/json");

$database = new Database();
$db = $database->getConnection();
$Projet = new Projet($db);

$data = $Projet->readAll();

if (!empty($data)) {
    $secteurs = [];

    foreach ($data as $row) {
        $secteur = $row['secteur_name'] ?? 'Non défini';
        $status  = strtolower($row['status'] ?? '');

        if (!isset($secteurs[$secteur])) {
            $secteurs[$secteur] = [
                'secteur' => $secteur,
                'total_projets' => 0,
                'total_realises' => 0,
                'taux_realisation' => 0
            ];
        }

        $secteurs[$secteur]['total_projets']++;
        if ($status === 'realise') $secteurs[$secteur]['total_realises']++;
    }

    foreach ($secteurs as &$sec) {
        if ($sec['total_projets'] > 0) {
            $sec['taux_realisation'] = round(
                ($sec['total_realises'] / $sec['total_projets']) * 100
            );
        } else {
            $sec['taux_realisation'] = 0;
        }
    }
    unset($sec);
    $total_projets = 0;
    $total_realises = 0;

    foreach ($secteurs as $sec) {
        $total_projets += $sec['total_projets'];
        $total_realises += $sec['total_realises'];
    }

    $taux_global = $total_projets > 0 ? round(($total_realises / $total_projets) * 100) : 0;
    $secteurs[] = [
        'secteur' => 'Total / moyenne',
        'total_projets' => $total_projets,
        'total_realises' => $total_realises,
        'taux_realisation' => $taux_global
    ];

    echo json_encode(['status' => 'success', 'message' => 'Statistiques des projets par secteur', 'data' => array_values($secteurs)]);
} else {
    echo json_encode(['status' => 'warning', 'message' => 'Aucun projet trouvé']);
}

$db = null;
exit;
