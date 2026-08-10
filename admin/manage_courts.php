<?php
include '../includes/auth_check.php';
requireLogin('admin');
include '../includes/db_connect.php';

$error = "";
$success = "";

// Handle Add Court
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_court'])) {
    $court_name = trim($_POST['court_name']);
    $price_per_hour = trim($_POST['price_per_hour']);

    if ($court_name == "" || $price_per_hour == "") {
        $error = "Please fill all fields.";
    } else {
        $insert = mysqli_prepare($conn, "INSERT INTO courts (court_name, price_per_hour) VALUES (?, ?)");
        mysqli_stmt_bind_param($insert, "sd", $court_name, $price_per_hour);
        if (mysqli_stmt_execute($insert)) {
            $success = "Court added successfully.";
        } else {
            $error = "Something went wrong while adding the court.";
        }
    }
}

// Handle Edit Court
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_court'])) {
    $court_id = $_POST['court_id'];
    $court_name = trim($_POST['court_name']);
    $price_per_hour = trim($_POST['price_per_hour']);

    if ($court_name == "" || $price_per_hour == "") {
        $error = "Please fill all fields.";
    } else {
        $update = mysqli_prepare($conn, "UPDATE courts SET court_name = ?, price_per_hour = ? WHERE court_id = ?");
        mysqli_stmt_bind_param($update, "sdi", $court_name, $price_per_hour, $court_id);
        if (mysqli_stmt_execute($update)) {
            $success = "Court updated successfully.";
        } else {
            $error = "Something went wrong while updating the court.";
        }
    }
}

// Handle Delete Court
if (isset($_GET['delete'])) {
    $court_id = $_GET['delete'];
    $delete = mysqli_prepare($conn, "DELETE FROM courts WHERE court_id = ?");
    mysqli_stmt_bind_param($delete, "i", $court_id);
    mysqli_stmt_execute($delete);
    header("Location: manage_courts.php");
    exit();
}

// Check if editing (to prefill the form)
$editCourt = null;
if (isset($_GET['edit'])) {
    $court_id = $_GET['edit'];
    $editQuery = mysqli_prepare($conn, "SELECT * FROM courts WHERE court_id = ?");
    mysqli_stmt_bind_param($editQuery, "i", $court_id);
    mysqli_stmt_execute($editQuery);
    $editResult = mysqli_stmt_get_result($editQuery);
    $editCourt = mysqli_fetch_assoc($editResult);
}

// Get all courts to display
$courtsResult = mysqli_query($conn, "SELECT * FROM courts ORDER BY court_id DESC");

include '../includes/header.php';
?>

<h1>Manage Courts</h1>

<?php if ($success != "") { ?>
    <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
<?php } ?>

<?php if ($error != "") { ?>
    <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
<?php } ?>

<div class="form-container">
    <h2><?php echo $editCourt ? "Edit Court" : "Add New Court"; ?></h2>

    <form method="POST" action="manage_courts.php">
        <?php if ($editCourt) { ?>
            <input type="hidden" name="court_id" value="<?php echo $editCourt['court_id']; ?>">
        <?php } ?>

        <label>Court Name</label>
        <input type="text" name="court_name" value="<?php echo $editCourt ? htmlspecialchars($editCourt['court_name']) : ''; ?>" required>

        <label>Price Per Hour (Rs.)</label>
        <input type="number" step="0.01" name="price_per_hour" value="<?php echo $editCourt ? htmlspecialchars($editCourt['price_per_hour']) : ''; ?>" required>

        <button type="submit" name="<?php echo $editCourt ? 'edit_court' : 'add_court'; ?>">
            <?php echo $editCourt ? "Update Court" : "Add Court"; ?>
        </button>
    </form>
</div>

<h2>All Courts</h2>

<table class="data-table">
    <tr>
        <th>Court Name</th>
        <th>Price Per Hour</th>
        <th>Actions</th>
    </tr>
    <?php while ($court = mysqli_fetch_assoc($courtsResult)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($court['court_name']); ?></td>
            <td>Rs. <?php echo htmlspecialchars($court['price_per_hour']); ?></td>
            <td>
                <a href="manage_courts.php?edit=<?php echo $court['court_id']; ?>">Edit</a>
                |
                <a href="manage_courts.php?delete=<?php echo $court['court_id']; ?>" onclick="return confirm('Are you sure you want to delete this court?');">Delete</a>
            </td>
        </tr>
    <?php } ?>
</table>

<?php include '../includes/footer.php'; ?>