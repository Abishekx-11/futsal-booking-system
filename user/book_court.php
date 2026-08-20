<?php
include '../includes/auth_check.php';
requireLogin('user');
include '../includes/db_connect.php';

$courtsResult = mysqli_query($conn, "SELECT * FROM courts ORDER BY court_name");

$selectedCourt = isset($_GET['court_id']) ? $_GET['court_id'] : '';
$selectedDate = isset($_GET['booking_date']) ? $_GET['booking_date'] : '';

$slots = [];

if ($selectedCourt != '' && $selectedDate != '') {
    $slotQuery = mysqli_prepare($conn, "
        SELECT time_slots.slot_id, time_slots.start_time, time_slots.end_time,
        (
            SELECT COUNT(*)
            FROM bookings
            WHERE bookings.court_id = ?
            AND bookings.slot_id = time_slots.slot_id
            AND bookings.booking_date = ?
            AND bookings.status IN ('Pending Payment', 'Advance Paid', 'Completed')
        ) AS is_booked
        FROM time_slots
        ORDER BY time_slots.start_time
    ");

    mysqli_stmt_bind_param(
        $slotQuery,
        "is",
        $selectedCourt,
        $selectedDate
    );

    mysqli_stmt_execute($slotQuery);
    $slotResult = mysqli_stmt_get_result($slotQuery);

    while ($row = mysqli_fetch_assoc($slotResult)) {
        $slots[] = $row;
    }
}

include '../includes/header.php';
?>

<h1>Book a Court</h1>

<div class="form-container">
    <h2>Select Court and Date</h2>

    <form method="GET" action="book_court.php">

        <label>Court</label>

        <select name="court_id" required>
            <option value="">-- Select Court --</option>

            <?php
            mysqli_data_seek($courtsResult, 0);

            while ($court = mysqli_fetch_assoc($courtsResult)) {
                $isSelected = ($selectedCourt == $court['court_id']) ? 'selected' : '';

                echo "<option value='" . $court['court_id'] . "' $isSelected>"
                    . htmlspecialchars($court['court_name'])
                    . " (Rs. "
                    . htmlspecialchars($court['price_per_hour'])
                    . "/hr)</option>";
            }
            ?>

        </select>

        <label>Date</label>

        <input
            type="date"
            name="booking_date"
            value="<?php echo htmlspecialchars($selectedDate); ?>"
            min="<?php echo date('Y-m-d'); ?>"
            required
        >

        <button type="submit">Check Availability</button>

    </form>
</div>

<?php if (!empty($slots)) { ?>

    <h2>Available Slots</h2>

    <form method="POST" action="confirm_booking.php">

        <input
            type="hidden"
            name="court_id"
            value="<?php echo htmlspecialchars($selectedCourt); ?>"
        >

        <input
            type="hidden"
            name="booking_date"
            value="<?php echo htmlspecialchars($selectedDate); ?>"
        >

        <div class="slot-grid">

            <?php foreach ($slots as $slot) { ?>

                <?php if ($slot['is_booked'] > 0) { ?>

                    <div class="slot-box slot-booked">

                        <?php
                        echo substr($slot['start_time'], 0, 5)
                            . " - "
                            . substr($slot['end_time'], 0, 5);
                        ?>

                        <br>Booked

                    </div>

                <?php } else { ?>

                    <label class="slot-box slot-available">

                        <input
                            type="radio"
                            name="slot_id"
                            value="<?php echo $slot['slot_id']; ?>"
                            required
                        >

                        <?php
                        echo substr($slot['start_time'], 0, 5)
                            . " - "
                            . substr($slot['end_time'], 0, 5);
                        ?>

                    </label>

                <?php } ?>

            <?php } ?>

        </div>

        <button type="submit">Confirm Selected Slot</button>

    </form>

<?php } elseif ($selectedCourt != '' && $selectedDate != '') { ?>

    <p>No slots found.</p>

<?php } ?>

<?php include '../includes/footer.php'; ?>