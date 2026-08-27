<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['admin_email'])) { header("Location: ../login.php"); exit(); }

if (isset($_GET['id'])) {
    $requestID = (int)$_GET['id'];
    mysqli_query($conn, "UPDATE BloodRequest SET Status='Rejected' WHERE RequestID=$requestID AND Status='Pending'");
}
header("Location: manage_requests.php");
exit();
?>