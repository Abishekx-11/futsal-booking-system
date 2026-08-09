<?php
include '../includes/auth_check.php';
requireLogin('admin');
include '../includes/header.php';
?>

<h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
<p>This is the admin dashboard. From here you can manage courts, manage court images, and view booking reports.</p>

<?php include '../includes/footer.php'; ?>