<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

if (!isset($_SESSION['booking_data'])) {
    header("Location: book_court.php");
    exit();
}

$bookingData = $_SESSION['booking_data'];

$court_id = $bookingData['court_id'];
$booking_date = $bookingData['booking_date'];
$slot_id = $bookingData['slot_id'];

$courtQuery = mysqli_prepare(
    $conn,
    "SELECT court_name, price_per_hour FROM courts WHERE court_id = ?"
);

mysqli_stmt_bind_param($courtQuery, "i", $court_id);
mysqli_stmt_execute($courtQuery);

$courtResult = mysqli_stmt_get_result($courtQuery);
$court = mysqli_fetch_assoc($courtResult);

$slotQuery = mysqli_prepare(
    $conn,
    "SELECT start_time, end_time FROM time_slots WHERE slot_id = ?"
);

mysqli_stmt_bind_param($slotQuery, "i", $slot_id);
mysqli_stmt_execute($slotQuery);

$slotResult = mysqli_stmt_get_result($slotQuery);
$slot = mysqli_fetch_assoc($slotResult);

if (!$court || !$slot) {
    unset($_SESSION['booking_data']);
    header("Location: book_court.php");
    exit();
}

$total_amount = $court['price_per_hour'];
$advance_amount = $total_amount * 0.30;
$remaining_amount = $total_amount - $advance_amount;

include '../includes/header.php';
?>

<h1>Choose Payment Option</h1>

<div class="form-container">

    <p><strong>Court:</strong> <?php echo htmlspecialchars($court['court_name']); ?></p>

    <p><strong>Date:</strong> <?php echo htmlspecialchars($booking_date); ?></p>

    <p>
        <strong>Time:</strong>
        <?php
        echo substr($slot['start_time'], 0, 5)
            . " - "
            . substr($slot['end_time'], 0, 5);
        ?>
    </p>

    <p><strong>Total Amount:</strong> Rs. <?php echo number_format($total_amount, 2); ?></p>

    <hr>

    <h2>Advance Payment</h2>

    <p>Pay 30% now: <strong>Rs. <?php echo number_format($advance_amount, 2); ?></strong></p>

    <p>
        The advance payment is non-refundable.
        The remaining Rs. <?php echo number_format($remaining_amount, 2); ?>
        must be paid at least 24 hours before the match.
    </p>

    <form method="POST" action="payment_confirmation.php">
        <input type="hidden" name="payment_type" value="Advance">

        <button type="submit">
            Pay Advance
        </button>
    </form>

    <hr>

    <h2>Full Payment</h2>

    <p>
        Pay the full amount now:
        <strong>Rs. <?php echo number_format($total_amount, 2); ?></strong>
    </p>

    <form method="POST" action="payment_confirmation.php">
        <input type="hidden" name="payment_type" value="Full">

        <button type="submit">
            Pay Full Amount
        </button>
    </form>

    <br>

    <a href="book_court.php?court_id=<?php echo $court_id; ?>&booking_date=<?php echo urlencode($booking_date); ?>">
        Back to Booking
    </a>

</div>

<?php include '../includes/footer.php'; ?>