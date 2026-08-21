<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['payment_type'])) {
    header("Location: book_court.php");
    exit();
}

if (!isset($_SESSION['booking_data'])) {
    header("Location: book_court.php");
    exit();
}

$payment_type = $_POST['payment_type'];

if ($payment_type !== 'Advance' && $payment_type !== 'Full') {
    header("Location: payment_selection.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$bookingData = $_SESSION['booking_data'];

$court_id = (int) $bookingData['court_id'];
$booking_date = $bookingData['booking_date'];
$slot_id = (int) $bookingData['slot_id'];

$availabilityQuery = mysqli_prepare($conn, "
    SELECT booking_id
    FROM bookings
    WHERE court_id = ?
    AND slot_id = ?
    AND booking_date = ?
    AND status IN ('Pending Payment', 'Advance Paid', 'Completed')
");

mysqli_stmt_bind_param(
    $availabilityQuery,
    "iis",
    $court_id,
    $slot_id,
    $booking_date
);

mysqli_stmt_execute($availabilityQuery);
mysqli_stmt_store_result($availabilityQuery);

if (mysqli_stmt_num_rows($availabilityQuery) > 0) {
    unset($_SESSION['booking_data']);

    include '../includes/header.php';
    ?>

    <p class="error-message">
        Sorry, this slot was just booked by someone else.
    </p>

    <a href="book_court.php">Back to Booking</a>

    <?php
    include '../includes/footer.php';
    exit();
}

$courtQuery = mysqli_prepare(
    $conn,
    "SELECT price_per_hour FROM courts WHERE court_id = ?"
);

mysqli_stmt_bind_param($courtQuery, "i", $court_id);
mysqli_stmt_execute($courtQuery);

$courtResult = mysqli_stmt_get_result($courtQuery);
$court = mysqli_fetch_assoc($courtResult);

if (!$court) {
    unset($_SESSION['booking_data']);
    header("Location: book_court.php");
    exit();
}

$slotQuery = mysqli_prepare(
    $conn,
    "SELECT start_time FROM time_slots WHERE slot_id = ?"
);

mysqli_stmt_bind_param($slotQuery, "i", $slot_id);
mysqli_stmt_execute($slotQuery);

$slotResult = mysqli_stmt_get_result($slotQuery);
$slot = mysqli_fetch_assoc($slotResult);

if (!$slot) {
    unset($_SESSION['booking_data']);
    header("Location: book_court.php");
    exit();
}

$total_amount = (float) $court['price_per_hour'];

if ($payment_type === 'Advance') {

    $amount_paid = $total_amount * 0.30;
    $remaining_amount = $total_amount - $amount_paid;
    $booking_status = 'Advance Paid';

    $matchDateTime = new DateTime(
        $booking_date . ' ' . $slot['start_time']
    );

    $payment_deadline = $matchDateTime
        ->modify('-24 hours')
        ->format('Y-m-d H:i:s');

} else {

    $amount_paid = $total_amount;
    $remaining_amount = 0.00;
    $booking_status = 'Completed';
    $payment_deadline = null;
}

$insertBooking = mysqli_prepare($conn, "
    INSERT INTO bookings (
        user_id,
        court_id,
        slot_id,
        booking_date,
        total_amount,
        amount_paid,
        remaining_amount,
        payment_deadline,
        status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

mysqli_stmt_bind_param(
    $insertBooking,
    "iiisdddss",
    $user_id,
    $court_id,
    $slot_id,
    $booking_date,
    $total_amount,
    $amount_paid,
    $remaining_amount,
    $payment_deadline,
    $booking_status
);

if (!mysqli_stmt_execute($insertBooking)) {
    include '../includes/header.php';
    ?>

    <p class="error-message">
        Something went wrong. Please try again.
    </p>

    <a href="payment_selection.php">Back</a>

    <?php
    include '../includes/footer.php';
    exit();
}

$booking_id = mysqli_insert_id($conn);

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
    $amount_paid,
    $payment_status
);

mysqli_stmt_execute($insertPayment);

unset($_SESSION['booking_data']);

header("Location: dashboard.php?payment_success=1");
exit();
?>