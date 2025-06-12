<?php
// api/logout.php  – terminate the current login session

session_start();

/* 1.  Remove all session variables */
$_SESSION = [];

/* 2.  Delete the session cookie (if one is in use) */
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,                 // expire in the past
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

/* 3.  Destroy the session on the server */
session_destroy();

/* 4.  Return a JSON payload (or redirect) */
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'success',
    'message' => 'Logged out successfully'
]);
