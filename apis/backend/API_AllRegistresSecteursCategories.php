<?php
$routePath = '../../';

require_once $routePath . 'config/database.php';
require_once $routePath . 'models/Register.php';
require_once $routePath . 'config/functions.php';


header("Content-Type: application/json");

$database = new Database();
$db = $database->getConnection();
$register = new Register($db);

$data = $register->readBySecteurCategorie();


if ($data !== false) {
    echo json_encode([
        'status' => empty($data) ? 'warning' : 'success',
        'message' => empty($data)
            ? 'Aucun registre trouvé'
            : 'Liste des registres',
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur lors de la récupération des données'
    ], JSON_UNESCAPED_UNICODE);
}

$db = null;
exit;
