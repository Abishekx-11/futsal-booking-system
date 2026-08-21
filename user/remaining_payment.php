<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

if (!isset($_GET['booking_id'])) {
    header("Location: booking_history.php");
    exit();
}

$booking_id = (int) $_GET['booking_id'];
$user_id = $_SESSION['user_id'];

$query = mysqli_prepare($conn, "
    SELECT
        bookings.booking_id,
        bookings.booking_date,
        bookings.remaining_amount,
        bookings.payment_deadline,
        bookings.status,
        courts.court_name,
        time_slots.start_time,
        time_slots.end_time
    FROM bookings
    JOIN courts ON bookings.court_id = courts.court_id
    JOIN time_slots ON bookings.slot_id = time_slots.slot_id
    WHERE bookings.booking_id = ?
    AND bookings.user_id = ?
    AND bookings.status = 'Advance Paid'
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

$startTime = date('g:i A', strtotime($booking['start_time']));
$endTime = date('g:i A', strtotime($booking['end_time']));

include '../includes/header.php';
?>

<h1>Complete Remaining Payment</h1>

<div class="form-container">

    <p>
        <strong>Court:</strong>
        <?php echo htmlspecialchars($booking['court_name']); ?>
    </p>

    <p>
        <strong>Date:</strong>
        <?php echo htmlspecialchars($booking['booking_date']); ?>
    </p>

    <p>
        <strong>Time:</strong>
        <?php echo $startTime . " - " . $endTime; ?>
    </p>

    <p>
        <strong>Remaining Amount:</strong>
        Rs. <?php echo number_format($booking['remaining_amount'], 2); ?>
    </p>

    <p>
        <strong>Payment Deadline:</strong>
        <?php echo date(
            'F j, Y g:i A',
            strtotime($booking['payment_deadline'])
        ); ?>
    </p>

    <p class="error-message">
        The remaining amount must be paid at least 24 hours before the match.
    </p>

    <form method="POST" action="process_remaining_payment.php">

        <input
            type="hidden"
            name="booking_id"
            value="<?php echo $booking['booking_id']; ?>"
        >

        <button type="submit">
            Pay Rs. <?php echo number_format($booking['remaining_amount'], 2); ?>
        </button>

    </form>

    <br>

    <a href="booking_history.php">Back to My Bookings</a>

</div>

<?php include '../includes/footer.php'; ?>