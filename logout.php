<?php
require_once 'config/config.php';

// Destroy session
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home page
redirect('index.php');
?>
