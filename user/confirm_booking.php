<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: book_court.php");
    exit();
}

$court_id = (int) $_POST['court_id'];
$booking_date = $_POST['booking_date'];
$slot_id = (int) $_POST['slot_id'];

if ($court_id <= 0 || $slot_id <= 0 || empty($booking_date)) {
    header("Location: book_court.php");
    exit();
}

$today = new DateTime();
$maxDate = new DateTime();
$maxDate->modify('+1 month');

$selectedDateObject = DateTime::createFromFormat('Y-m-d', $booking_date);

if (
    !$selectedDateObject ||
    $selectedDateObject->format('Y-m-d') !== $booking_date ||
    $selectedDateObject < $today ||
    $selectedDateObject > $maxDate
) {
    include '../includes/header.php';
    ?>

    <p class="error-message">
        Please select a booking date within 1 month from today.
    </p>

    <a href="book_court.php">Back to Booking</a>

    <?php
    include '../includes/footer.php';
    exit();
}

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
    include '../includes/header.php';
    ?>

    <p class="error-message">
        Sorry, this slot was just booked by someone else.
    </p>

    <a href="book_court.php?court_id=<?php echo $court_id; ?>&booking_date=<?php echo urlencode($booking_date); ?>">
        Back to Booking
    </a>

    <?php
    include '../includes/footer.php';
    exit();
}

$courtQuery = mysqli_prepare(
    $conn,
    "SELECT court_name, price_per_hour FROM courts WHERE court_id = ?"
);

mysqli_stmt_bind_param($courtQuery, "i", $court_id);
mysqli_stmt_execute($courtQuery);

$courtResult = mysqli_stmt_get_result($courtQuery);
$court = mysqli_fetch_assoc($courtResult);

if (!$court) {
    header("Location: book_court.php");
    exit();
}

$slotQuery = mysqli_prepare(
    $conn,
    "SELECT start_time, end_time FROM time_slots WHERE slot_id = ?"
);

mysqli_stmt_bind_param($slotQuery, "i", $slot_id);
mysqli_stmt_execute($slotQuery);

$slotResult = mysqli_stmt_get_result($slotQuery);
$slot = mysqli_fetch_assoc($slotResult);

if (!$slot) {
    header("Location: book_court.php");
    exit();
}

$_SESSION['booking_data'] = [
    'court_id' => $court_id,
    'booking_date' => $booking_date,
    'slot_id' => $slot_id
];

header("Location: payment_selection.php");
exit();
?>