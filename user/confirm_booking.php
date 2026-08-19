<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: book_court.php");
    exit();
}

$court_id = $_POST['court_id'];
$booking_date = $_POST['booking_date'];
$slot_id = $_POST['slot_id'];
$user_id = $_SESSION['user_id'];

$insert = mysqli_prepare($conn, "INSERT INTO bookings (user_id, court_id, slot_id, booking_date, status) VALUES (?, ?, ?, ?, 'Pending')");
mysqli_stmt_bind_param($insert, "iiis", $user_id, $court_id, $slot_id, $booking_date);

if (mysqli_stmt_execute($insert)) {
    $booking_id = mysqli_insert_id($conn);
    header("Location: ../user/dashboard.php?booked=1");
    exit();
} else {
    if (mysqli_errno($conn) == 1062) {
        $error = "Sorry, this slot was just booked by someone else. Please choose another slot.";
    } else {
        $error = "Something went wrong. Please try again.";
    }
    include '../includes/header.php';
    ?>
    <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <a href="book_court.php">Back to Booking</a>
    <?php
    include '../includes/footer.php';
}
?>