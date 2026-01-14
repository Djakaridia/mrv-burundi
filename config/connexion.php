

<?php
require_once 'jwt-token.php';
require_once 'database.php';
require_once 'models/User.php';

function checkAuth()
{
    $token = $_COOKIE['authtkmrv'] ?? null;

    if (!$token) {
        http_response_code(401);
        header('Location: logout.php?error=session_expiree');
        exit();
    }

    if (!preg_match('/^[a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_]+$/', $token)) {
        http_response_code(401);
        header('Location: logout.php?error=token_invalide');
        exit();
    }

    if (!isset($_SESSION['user-data'])) {
        http_response_code(401);
        header('Location: logout.php?error=session_invalide');
        exit();
    }

    try {
        $payload = JWTUtils::validateJWT($token);

        if ($payload['user_id'] !== ($_SESSION['user-data']['user-id'] ?? null)) {
            http_response_code(401);
            header('Location: logout.php?error=session_corrompue');
            exit();
        }

        return $payload;
    } catch (Exception $e) {
        http_response_code(401);
        header('Location: logout.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}

function autoAuth()
{
    $token = $_COOKIE['authtkmrv'] ?? null;

    if ($token) {
        try {
            $payload = JWTUtils::validateJWT($token);
            
            http_response_code(200);
            header('Location: redirect.php?tk=' . urlencode($token));
            exit();
        } catch (Exception $e) {
            http_response_code(401);
            header('Location: logout.php?error=' . urlencode($e->getMessage()));
            exit();
        }
    }
}
