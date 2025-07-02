<?php
require_once '../config/functions.php';

// Destroy admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_role']);

// If session is empty, destroy it completely
if (empty($_SESSION)) {
    session_destroy();
}

// Redirect to admin login
header('Location: login.php');
exit;
?>