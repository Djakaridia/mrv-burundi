<?php
$routePath = '../../';

require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Inventory.php';
require_once $routePath . 'config/functions.php';


header("Content-Type: application/json");

$database = new Database();
$db = $database->getConnection();
$inventory = new Inventory($db);

$data = $inventory->AllDataParSecteur();

if (!empty($data)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Liste des inventaires',
        'data' => $data
    ]);
} else {
    echo json_encode([
        'status' => 'warning',
        'message' => 'Aucun inventaire trouvé'
    ]);
}

$db = null;
exit;
