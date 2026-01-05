<?php
session_start();

$error = $_GET['error'] ?? 'no';
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

setcookie('authtkmrv', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_unset();
session_destroy();

echo "<script>localStorage.removeItem('authtkmrv');</script>";
header("Location: index.php?error=" . urlencode($error));
exit();
