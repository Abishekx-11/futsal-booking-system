<?php
include '../includes/db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    if ($name == "" || $email == "" || $password == "") {
        $error = "Please fill all required fields.";
    } else {
        $emailQuery = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($emailQuery, "s", $email);
        mysqli_stmt_execute($emailQuery);
        mysqli_stmt_store_result($emailQuery);

        if (mysqli_stmt_num_rows($emailQuery) > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insertQuery = mysqli_prepare($conn, "INSERT INTO users (name, email, phone_number, password) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($insertQuery, "ssss", $name, $email, $phone, $hashedPassword);

            if (mysqli_stmt_execute($insertQuery)) {
                header("Location: user_login.php?registered=1");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - Futsal Booking System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Create an Account</h2>

        <?php if ($error != "") { ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>

        <form method="POST" action="signup.php">
            <label>Full Name</label>
            <input type="text" name="name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Phone Number</label>
            <input type="text" name="phone">

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Sign Up</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>