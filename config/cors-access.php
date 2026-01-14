<?php
function configureCORS() {
    $config = [
        'allowed_origins' => [
            'https://admin.mrv-burundi.com',
            'https://fiche.mrv-mali.com',
        ],
        'allowed_methods' => ['POST', 'GET', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'max_age' => 3600,
        'allow_credentials' => true
    ];

    if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $config['allowed_origins'])) {
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
        header("Vary: Origin");
    }

    header("Access-Control-Allow-Methods: " . implode(', ', $config['allowed_methods']));
    header("Access-Control-Allow-Headers: " . implode(', ', $config['allowed_headers']));
    header("Access-Control-Max-Age: " . $config['max_age']);
    header("Access-Control-Allow-Credentials: " . ($config['allow_credentials'] ? 'true' : 'false'));

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(204);
        exit();
    }
}