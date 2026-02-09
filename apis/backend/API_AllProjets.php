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
    echo json_encode([
        'status' => 'success',
        'message' => 'Liste des actions',
        'data' => $data
    ]);
} else {
    echo json_encode([
        'status' => 'warning',
        'message' => 'Aucune action trouvée'
    ]);
}

$db = null;
exit;
