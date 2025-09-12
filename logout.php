<?php
// Securely log out admin by clearing the refresh_token cookie and redirecting to login
setcookie('refresh_token', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'domain' => '',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'None' : 'Strict'
]);
header('Location: admin_login.php');
exit;
