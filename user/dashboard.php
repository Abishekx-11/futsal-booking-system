<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/header.php';
?>

<h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
<p>This is your dashboard. From here you can book a court, view your bookings, check the gallery, or update your profile.</p>

<?php include '../includes/footer.php'; ?>