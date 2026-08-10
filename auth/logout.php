<?php
session_start();
session_unset();
session_destroy();
header("Location: /futsal-booking-system/index.php");
exit();
?>