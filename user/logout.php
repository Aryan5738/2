<?php
require_once '../config/functions.php';

// Destroy all session data
session_destroy();

// Clear any cookies if set
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to home page
header('Location: ../index.php');
exit;
?>