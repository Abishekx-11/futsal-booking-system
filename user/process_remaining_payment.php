<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['booking_id'])) {
    header("Location: booking_history.php");
    exit();
}

$booking_id = (int) $_POST['booking_id'];
$user_id = $_SESSION['user_id'];

$query = mysqli_prepare($conn, "
    SELECT
        booking_id,
        remaining_amount,
        payment_deadline,
        status
    FROM bookings
    WHERE booking_id = ?
    AND user_id = ?
    AND status = 'Advance Paid'
");

mysqli_stmt_bind_param($query, "ii", $booking_id, $user_id);
mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    header("Location: booking_history.php");
    exit();
}

if (strtotime($booking['payment_deadline']) <= time()) {
    header("Location: booking_history.php?error=deadline_passed");
    exit();
}

$remaining_amount = (float) $booking['remaining_amount'];

mysqli_begin_transaction($conn);

try {

    $updateBooking = mysqli_prepare($conn, "
        UPDATE bookings
        SET
            amount_paid = amount_paid + ?,
            remaining_amount = 0,
            payment_deadline = NULL,
            status = 'Completed'
        WHERE booking_id = ?
        AND user_id = ?
        AND status = 'Advance Paid'
    ");

    mysqli_stmt_bind_param(
        $updateBooking,
        "dii",
        $remaining_amount,
        $booking_id,
        $user_id
    );

    if (!mysqli_stmt_execute($updateBooking)) {
        throw new Exception("Booking update failed.");
    }

    $payment_type = 'Remaining';
    $payment_status = 'Paid';

    $insertPayment = mysqli_prepare($conn, "
        INSERT INTO payments (
            booking_id,
            payment_type,
            amount,
            payment_status
        )
        VALUES (?, ?, ?, ?)
    ");

    mysqli_stmt_bind_param(
        $insertPayment,
        "isds",
        $booking_id,
        $payment_type,
        $remaining_amount,
        $payment_status
    );

    if (!mysqli_stmt_execute($insertPayment)) {
        throw new Exception("Payment record failed.");
    }

    mysqli_commit($conn);

    header("Location: booking_history.php?remaining_payment_success=1");
    exit();

} catch (Exception $e) {

    mysqli_rollback($conn);

    header("Location: booking_history.php?error=payment_failed");
    exit();
}
?>