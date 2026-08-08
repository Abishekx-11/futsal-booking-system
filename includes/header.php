<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Futsal Booking System</title>
    <link rel="stylesheet" href="/futsal-booking-system/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="logo">Futsal Booking System</div>

        <nav>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'user') { ?>
                <a href="/futsal-booking-system/user/dashboard.php">Dashboard</a>
                <a href="/futsal-booking-system/user/book_court.php">Book a Court</a>
                <a href="/futsal-booking-system/user/booking_history.php">My Bookings</a>
                <a href="/futsal-booking-system/user/gallery.php">Gallery</a>
                <a href="/futsal-booking-system/user/profile.php">Profile</a>
                <a href="/futsal-booking-system/auth/logout.php">Logout</a>
            <?php } elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
                <a href="/futsal-booking-system/admin/dashboard.php">Dashboard</a>
                <a href="/futsal-booking-system/admin/manage_courts.php">Manage Courts</a>
                <a href="/futsal-booking-system/admin/manage_court_images.php">Court Images</a>
                <a href="/futsal-booking-system/admin/view_reports.php">Reports</a>
                <a href="/futsal-booking-system/auth/logout.php">Logout</a>
            <?php } else { ?>
                <a href="/futsal-booking-system/index.php">Home</a>
            <?php } ?>
        </nav>
    </header>

    <main class="page-content">