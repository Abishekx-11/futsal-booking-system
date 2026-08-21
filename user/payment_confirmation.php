<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['payment_type'])) {
    header("Location: payment_selection.php");
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

$bookingData = $_SESSION['booking_data'];

$court_id = (int) $bookingData['court_id'];
$booking_date = $bookingData['booking_date'];
$slot_id = (int) $bookingData['slot_id'];

$courtQuery = mysqli_prepare(
    $conn,
    "SELECT court_name, price_per_hour FROM courts WHERE court_id = ?"
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

$total_amount = (float) $court['price_per_hour'];

if ($payment_type === 'Advance') {
    $payment_amount = $total_amount * 0.30;
} else {
    $payment_amount = $total_amount;
}

include '../includes/header.php';
?>

<h1>Payment Confirmation</h1>

<div class="form-container">

    <p><strong>Court:</strong> <?php echo htmlspecialchars($court['court_name']); ?></p>

    <p><strong>Date:</strong> <?php echo htmlspecialchars($booking_date); ?></p>

    <p><strong>Payment Type:</strong> <?php echo htmlspecialchars($payment_type); ?></p>

    <p>
        <strong>Amount to Pay:</strong>
        Rs. <?php echo number_format($payment_amount, 2); ?>
    </p>

    <hr>

    <h2>ConnectIPS Payment</h2>

    <p>
        Payment demonstration will be shown here.
    </p>

    <form method="POST" action="process_payment.php">

        <input
            type="hidden"
            name="payment_type"
            value="<?php echo htmlspecialchars($payment_type); ?>"
        >

        <button type="submit">
            I Have Completed Payment
        </button>

    </form>

    <br>

    <form method="POST" action="payment_selection.php">

        <button type="submit">
            Back to Payment Selection
        </button>

    </form>

</div>

<?php include '../includes/footer.php'; ?>