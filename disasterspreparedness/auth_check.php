<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_login() {
    if (empty($_SESSION['user_id'])) {
        
        header('Location: login.php');
        exit;
    }
}


function require_admin() {
    require_login();
    if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    
        header('HTTP/1.1 403 Forbidden');
        echo "403 Forbidden - You don't have permission to access this page.";
        exit;
    }
}
