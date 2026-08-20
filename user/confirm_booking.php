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
$user_id = $_SESSION['user_id'];


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

    $error = "Sorry, this slot was just booked by someone else. Please choose another slot.";

} else {

    $courtQuery = mysqli_prepare(
        $conn,
        "SELECT price_per_hour FROM courts WHERE court_id = ?"
    );

    mysqli_stmt_bind_param($courtQuery, "i", $court_id);
    mysqli_stmt_execute($courtQuery);

    $courtResult = mysqli_stmt_get_result($courtQuery);
    $court = mysqli_fetch_assoc($courtResult);

    if (!$court) {

        $error = "Court not found.";

    } else {

        $total_amount = $court['price_per_hour'];

        $amount_paid = 0.00;
        $remaining_amount = $total_amount;

        $insert = mysqli_prepare($conn, "
            INSERT INTO bookings (
                user_id,
                court_id,
                slot_id,
                booking_date,
                total_amount,
                amount_paid,
                remaining_amount,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending Payment')
        ");

        mysqli_stmt_bind_param(
            $insert,
            "iiisddd",
            $user_id,
            $court_id,
            $slot_id,
            $booking_date,
            $total_amount,
            $amount_paid,
            $remaining_amount
        );

        if (mysqli_stmt_execute($insert)) {

            header("Location: ../user/dashboard.php?booked=1");
            exit();

        } else {

            $error = "Something went wrong. Please try again.";

        }
    }
}


include '../includes/header.php';
?>

<p class="error-message">
    <?php echo htmlspecialchars($error); ?>
</p>

<a href="book_court.php">Back to Booking</a>

<?php include '../includes/footer.php'; ?>