<?php
/**
 * LOCATION: /auth/logout.php
 */
session_start();

// 1. Clear all session data
$_SESSION = array();

// 2. Destroy the session cookie in the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session on the server
session_destroy();

// 4. Redirect to login (Move up one level from /auth/ to reach index.php)
header("Location: ../index.php?msg=logged_out");
exit();