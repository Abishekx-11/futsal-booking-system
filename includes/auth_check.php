<?php
session_start();

function requireLogin($requiredRole) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != $requiredRole) {
        header("Location: /futsal-booking-system/auth/login.php");
        exit();
    }
}
?>