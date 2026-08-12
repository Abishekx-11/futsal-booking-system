<?php
include '../includes/auth_check.php';
requireLogin('admin');
include '../includes/db_connect.php';

$error = "";
$success = "";

// Handle Image Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_image'])) {
    $court_id = $_POST['court_id'];

    if (!isset($_FILES['court_image']) || $_FILES['court_image']['error'] != 0) {
        $error = "Please select an image to upload.";
    } else {
        $fileName = $_FILES['court_image']['name'];
        $fileTmpPath = $_FILES['court_image']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExt, $allowedExtensions)) {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } else {
            $newFileName = uniqid('court_', true) . '.' . $fileExt;
            $uploadPath = '../assets/uploads/court_images/' . $newFileName;

            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                $dbPath = 'assets/uploads/court_images/' . $newFileName;
                $insert = mysqli_prepare($conn, "INSERT INTO court_images (court_id, image_path) VALUES (?, ?)");
                mysqli_stmt_bind_param($insert, "is", $court_id, $dbPath);
                mysqli_stmt_execute($insert);
                $success = "Image uploaded successfully.";
            } else {
                $error = "Failed to upload image. Try again.";
            }
        }
    }
}

// Handle Image Delete
if (isset($_GET['delete'])) {
    $image_id = $_GET['delete'];

    $imgQuery = mysqli_prepare($conn, "SELECT image_path FROM court_images WHERE image_id = ?");
    mysqli_stmt_bind_param($imgQuery, "i", $image_id);
    mysqli_stmt_execute($imgQuery);
    $imgResult = mysqli_stmt_get_result($imgQuery);
    $img = mysqli_fetch_assoc($imgResult);

    if ($img) {
        $filePath = '../' . $img['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $delete = mysqli_prepare($conn, "DELETE FROM court_images WHERE image_id = ?");
        mysqli_stmt_bind_param($delete, "i", $image_id);
        mysqli_stmt_execute($delete);
    }

    header("Location: manage_court_images.php");
    exit();
}

// Get all courts (for the dropdown)
$courtsResult = mysqli_query($conn, "SELECT * FROM courts ORDER BY court_name");

// Get all images with their court name
$imagesResult = mysqli_query($conn, "SELECT court_images.*, courts.court_name FROM court_images JOIN courts ON court_images.court_id = courts.court_id ORDER BY court_images.image_id DESC");

include '../includes/header.php';
?>

<h1>Manage Court Images</h1>

<?php if ($success != "") { ?>
    <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
<?php } ?>

<?php if ($error != "") { ?>
    <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
<?php } ?>

<div class="form-container">
    <h2>Upload New Image</h2>

    <form method="POST" action="manage_court_images.php" enctype="multipart/form-data">
        <label>Select Court</label>
        <select name="court_id" required>
            <?php while ($court = mysqli_fetch_assoc($courtsResult)) { ?>
                <option value="<?php echo $court['court_id']; ?>"><?php echo htmlspecialchars($court['court_name']); ?></option>
            <?php } ?>
        </select>

        <label>Image File</label>
        <input type="file" name="court_image" accept=".jpg,.jpeg,.png,.gif" required>

        <button type="submit" name="upload_image">Upload Image</button>
    </form>
</div>

<h2>All Court Images</h2>

<div class="image-gallery">
    <?php while ($image = mysqli_fetch_assoc($imagesResult)) { ?>
        <div class="image-card">
            <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="Court Image">
            <p><?php echo htmlspecialchars($image['court_name']); ?></p>
            <a href="manage_court_images.php?delete=<?php echo $image['image_id']; ?>" onclick="return confirm('Delete this image?');">Delete</a>
        </div>
    <?php } ?>
</div>

<?php include '../includes/footer.php'; ?>