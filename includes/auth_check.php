<?php
session_start();

function requireLogin($requiredRole) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != $requiredRole) {
        if ($requiredRole == 'admin') {
            header("Location: /futsal-booking-system/auth/admin_login.php");
        } else {
            header("Location: /futsal-booking-system/auth/user_login.php");
        }
        exit();
    }
}
?>