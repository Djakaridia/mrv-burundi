<?php
$routePath = '../../';

require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Register.php';
require_once $routePath . 'config/functions.php';


header("Content-Type: application/json");

$database = new Database();
$db = $database->getConnection();
$register = new Register($db);

$data = $register->readByCategorie();

if (!empty($data)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Liste des registres',
        'data' => $data
    ]);
} else {
    echo json_encode([
        'status' => 'warning',
        'message' => 'Aucune registre trouvée'
    ]);
}

$db = null;
exit;
