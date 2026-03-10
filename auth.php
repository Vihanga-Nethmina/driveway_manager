<?php
// config/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login() {
    if (empty($_SESSION['user']) || !is_array($_SESSION['user'])) {
        session_destroy();
        header("Location: login.php");
        exit;
    }
}

function require_role($role) {
    require_login();
    if (!is_array($_SESSION['user']) || (isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : '') !== $role) {
        header("Location: dashboard.php");
        exit;
    }
}
?>