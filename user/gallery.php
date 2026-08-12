<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

$imagesResult = mysqli_query($conn, "SELECT court_images.*, courts.court_name FROM court_images JOIN courts ON court_images.court_id = courts.court_id ORDER BY court_images.image_id DESC");

include '../includes/header.php';
?>

<h1>Court Gallery</h1>

<?php if (mysqli_num_rows($imagesResult) == 0) { ?>
    <p>No images have been uploaded yet.</p>
<?php } ?>

<div class="image-gallery">
    <?php while ($image = mysqli_fetch_assoc($imagesResult)) { ?>
        <div class="image-card">
            <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="Court Image">
            <p><?php echo htmlspecialchars($image['court_name']); ?></p>
        </div>
    <?php } ?>
</div>

<?php include '../includes/footer.php'; ?>