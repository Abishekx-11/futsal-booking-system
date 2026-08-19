<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_picture'])) {
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] != 0) {
        $error = "Please select an image to upload.";
    } else {
        $fileName = $_FILES['profile_picture']['name'];
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExt, $allowedExtensions)) {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } else {
            $newFileName = uniqid('profile_', true) . '.' . $fileExt;
            $uploadPath = '../assets/uploads/profile_pictures/' . $newFileName;

            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                $oldPicQuery = mysqli_prepare($conn, "SELECT profile_picture FROM users WHERE user_id = ?");
                mysqli_stmt_bind_param($oldPicQuery, "i", $user_id);
                mysqli_stmt_execute($oldPicQuery);
                $oldPicResult = mysqli_stmt_get_result($oldPicQuery);
                $oldPicRow = mysqli_fetch_assoc($oldPicResult);

                if ($oldPicRow['profile_picture'] && file_exists('../' . $oldPicRow['profile_picture'])) {
                    unlink('../' . $oldPicRow['profile_picture']);
                }

                $dbPath = 'assets/uploads/profile_pictures/' . $newFileName;
                $updatePic = mysqli_prepare($conn, "UPDATE users SET profile_picture = ? WHERE user_id = ?");
                mysqli_stmt_bind_param($updatePic, "si", $dbPath, $user_id);
                mysqli_stmt_execute($updatePic);

                $success = "Profile picture updated successfully.";
            } else {
                $error = "Failed to upload image. Try again.";
            }
        }
    }
}

// Handle profile detail update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_details'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    if ($name == "") {
        $error = "Name cannot be empty.";
    } else {
        $update = mysqli_prepare($conn, "UPDATE users SET name = ?, phone_number = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($update, "ssi", $name, $phone, $user_id);
        mysqli_stmt_execute($update);

        $_SESSION['name'] = $name;
        $success = "Profile updated successfully.";
    }
}

// Fetch current user data
$userQuery = mysqli_prepare($conn, "SELECT * FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($userQuery, "i", $user_id);
mysqli_stmt_execute($userQuery);
$userResult = mysqli_stmt_get_result($userQuery);
$user = mysqli_fetch_assoc($userResult);

include '../includes/header.php';
?>

<h1>My Profile</h1>

<?php if ($success != "") { ?>
    <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
<?php } ?>

<?php if ($error != "") { ?>
    <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
<?php } ?>

<div class="form-container">
    <h2>Profile Picture</h2>

    <?php if ($user['profile_picture']) { ?>
        <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-pic">
    <?php } else { ?>
        <p>No profile picture uploaded yet.</p>
    <?php } ?>

    <form method="POST" action="profile.php" enctype="multipart/form-data">
        <label>Upload New Picture</label>
        <input type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.gif" required>
        <button type="submit" name="upload_picture">Upload</button>
    </form>
</div>

<div class="form-container">
    <h2>Edit Details</h2>

    <form method="POST" action="profile.php">
        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

        <label>Email</label>
        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>

        <label>Phone Number</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone_number']); ?>">

        <button type="submit" name="update_details">Save Changes</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>