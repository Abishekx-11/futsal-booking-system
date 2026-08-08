<?php
session_start();
include '../includes/db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $adminQuery = mysqli_prepare($conn, "SELECT admin_id, name, password FROM admins WHERE email = ?");
    mysqli_stmt_bind_param($adminQuery, "s", $email);
    mysqli_stmt_execute($adminQuery);
    $adminResult = mysqli_stmt_get_result($adminQuery);
    $admin = mysqli_fetch_assoc($adminResult);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['role'] = 'admin';
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['name'] = $admin['name'];
        header("Location: ../admin/dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Futsal Booking System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Court Admin Login</h2>

        <?php if ($error != "") { ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>

        <form method="POST" action="admin_login.php">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <p><a href="../index.php">Back</a></p>
    </div>
</body>
</html>