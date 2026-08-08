<?php
session_start();
include '../includes/db_connect.php';

$error = "";
$success = "";

if (isset($_GET['registered'])) {
    $success = "Account created successfully. Please log in.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $userQuery = mysqli_prepare($conn, "SELECT user_id, name, password FROM users WHERE email = ?");
    mysqli_stmt_bind_param($userQuery, "s", $email);
    mysqli_stmt_execute($userQuery);
    $userResult = mysqli_stmt_get_result($userQuery);
    $user = mysqli_fetch_assoc($userResult);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['role'] = 'user';
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        header("Location: ../user/dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Futsal Booking System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Customer Login</h2>

        <?php if ($success != "") { ?>
            <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
        <?php } ?>

        <?php if ($error != "") { ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>

        <form method="POST" action="user_login.php">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
        <p><a href="../index.php">Back</a></p>
    </div>
</body>
</html>