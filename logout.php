<?php
/**
 * RealHome - Logout Script
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';

if (is_logged_in()) {
    log_activity((int)$_SESSION['user_id'], 'User Logout', 'User logged out.');
}

// Unset session variables
$_SESSION = [];

// Destroy session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start clean session for flash message
session_start();
set_flash('info', 'You have been successfully logged out. See you soon!');
header('Location: ' . BASE_URL . 'login.php');
exit;
