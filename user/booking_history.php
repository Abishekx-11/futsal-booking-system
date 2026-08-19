<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

$user_id = $_SESSION['user_id'];

$query = mysqli_prepare($conn, "
    SELECT bookings.booking_id, bookings.booking_date, bookings.status,
           time_slots.start_time, time_slots.end_time,
           courts.court_name, courts.price_per_hour,
           payments.payment_status
    FROM bookings
    JOIN courts ON bookings.court_id = courts.court_id
    JOIN time_slots ON bookings.slot_id = time_slots.slot_id
    LEFT JOIN payments ON bookings.booking_id = payments.booking_id
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
            <th>Price</th>
            <th>Booking Status</th>
            <th>Payment Status</th>
            <th>Action</th>
        </tr>
        <?php while ($booking = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($booking['court_name']); ?></td>
                <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                <td><?php echo substr($booking['start_time'], 0, 5) . " - " . substr($booking['end_time'], 0, 5); ?></td>
                <td>Rs. <?php echo htmlspecialchars($booking['price_per_hour']); ?></td>
                <td><?php echo htmlspecialchars($booking['status']); ?></td>
                <td><?php echo $booking['payment_status'] ? htmlspecialchars($booking['payment_status']) : 'N/A'; ?></td>
                <td>
                    <?php
                    $bookingDateTime = strtotime($booking['booking_date'] . ' ' . $booking['start_time']);
                    $cutoffTime = $bookingDateTime - (2 * 60 * 60);

                    if ($booking['status'] == 'Confirmed' && time() < $cutoffTime) {
                        echo '<a href="cancel_booking.php?booking_id=' . $booking['booking_id'] . '" onclick="return confirm(\'Cancel this booking?\');">Cancel</a>';
                    } elseif ($booking['status'] == 'Cancelled') {
                        echo '—';
                    } else {
                        echo 'Cannot cancel';
                    }
                    ?>
                </td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>

<?php include '../includes/footer.php'; ?>