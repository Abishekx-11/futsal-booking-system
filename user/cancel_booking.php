<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

if (!isset($_GET['booking_id'])) {
    header("Location: booking_history.php");
    exit();
}

$booking_id = $_GET['booking_id'];
$user_id = $_SESSION['user_id'];

$query = mysqli_prepare($conn, "
    SELECT bookings.booking_date, time_slots.start_time
    FROM bookings
    JOIN time_slots ON bookings.slot_id = time_slots.slot_id
    WHERE bookings.booking_id = ? AND bookings.user_id = ? AND bookings.status = 'Confirmed'
");
mysqli_stmt_bind_param($query, "ii", $booking_id, $user_id);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    header("Location: booking_history.php");
    exit();
}

$bookingDateTime = strtotime($booking['booking_date'] . ' ' . $booking['start_time']);
$cutoffTime = $bookingDateTime - (2 * 60 * 60);

if (time() >= $cutoffTime) {
    header("Location: booking_history.php?error=toolate");
    exit();
}

$updateBooking = mysqli_prepare($conn, "UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ?");
mysqli_stmt_bind_param($updateBooking, "i", $booking_id);
mysqli_stmt_execute($updateBooking);

$updatePayment = mysqli_prepare($conn, "UPDATE payments SET payment_status = 'Refunded' WHERE booking_id = ? AND payment_status = 'Paid'");
mysqli_stmt_bind_param($updatePayment, "i", $booking_id);
mysqli_stmt_execute($updatePayment);

header("Location: booking_history.php?cancelled=1");
exit();
?>