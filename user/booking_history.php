<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

$user_id = $_SESSION['user_id'];

$query = mysqli_prepare($conn, "
    SELECT 
        bookings.booking_id,
        bookings.booking_date,
        bookings.status,
        bookings.total_amount,
        bookings.amount_paid,
        bookings.remaining_amount,
        time_slots.start_time,
        time_slots.end_time,
        courts.court_name
    FROM bookings
    JOIN courts ON bookings.court_id = courts.court_id
    JOIN time_slots ON bookings.slot_id = time_slots.slot_id
    WHERE bookings.user_id = ?
    ORDER BY bookings.booking_date DESC, time_slots.start_time DESC
");

mysqli_stmt_bind_param($query, "i", $user_id);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);

include '../includes/header.php';
?>

<h1>My Bookings</h1>

<?php if (mysqli_num_rows($result) == 0) { ?>

    <p>You have no bookings yet.</p>

<?php } else { ?>

    <table class="data-table">

        <tr>
            <th>Court</th>
            <th>Date</th>
            <th>Time Slot</th>
            <th>Total Amount</th>
            <th>Paid</th>
            <th>Remaining</th>
            <th>Booking Status</th>
            <th>Action</th>
        </tr>

        <?php while ($booking = mysqli_fetch_assoc($result)) { ?>

            <?php
            $startTime = date('g:i A', strtotime($booking['start_time']));
            $endTime = date('g:i A', strtotime($booking['end_time']));

            $bookingDateTime = strtotime(
                $booking['booking_date'] . ' ' . $booking['start_time']
            );

            $cutoffTime = $bookingDateTime - (2 * 60 * 60);
            ?>

            <tr>

                <td>
                    <?php echo htmlspecialchars($booking['court_name']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($booking['booking_date']); ?>
                </td>

                <td>
                    <?php echo $startTime . " - " . $endTime; ?>
                </td>

                <td>
                    Rs. <?php echo number_format($booking['total_amount'], 2); ?>
                </td>

                <td>
                    Rs. <?php echo number_format($booking['amount_paid'], 2); ?>
                </td>

                <td>
                    Rs. <?php echo number_format($booking['remaining_amount'], 2); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($booking['status']); ?>
                </td>

                <td>

    <?php if ($booking['status'] == 'Advance Paid') { ?>

        <a href="remaining_payment.php?booking_id=<?php echo $booking['booking_id']; ?>">
            Pay Remaining Amount
        </a>

        <br><br>

        <?php if (time() < $cutoffTime) { ?>

            <a
                href="cancel_booking.php?booking_id=<?php echo $booking['booking_id']; ?>"
                onclick="return confirm('Are you sure you want to cancel this booking?\n\nYour advance payment is non-refundable. Once cancelled, your booking will be released and this slot may be booked by another user.');"
            >
                Cancel
            </a>

        <?php } ?>

    <?php } elseif (
        $booking['status'] == 'Completed'
        && time() < $cutoffTime
    ) { ?>

        <a
            href="cancel_booking.php?booking_id=<?php echo $booking['booking_id']; ?>"
            onclick="return confirm('Are you sure you want to cancel this booking?\n\nYou will receive a 95% refund of your total payment. A 5% deduction will be applied to cover payment processing and booking administration charges.');"
        >
            Cancel
        </a>

    <?php } elseif ($booking['status'] == 'Cancelled') { ?>

        —

    <?php } else { ?>

        Cannot cancel

    <?php } ?>

</td>

            </tr>

        <?php } ?>

    </table>

<?php } ?>

<?php include '../includes/footer.php'; ?>