<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['admin_email'])) { header("Location: ../login.php"); exit(); }

if (isset($_GET['id'])) {
    $requestID = (int)$_GET['id'];

    $requestQuery = mysqli_query($conn, "SELECT * FROM BloodRequest WHERE RequestID = $requestID");
    $request = mysqli_fetch_assoc($requestQuery);

    if ($request && $request['Status'] === 'Pending') {
        $bloodGroup = $request['BloodGroup'];
        $unitsRequested = (int)$request['UnitsRequested'];

        $stockQuery = mysqli_prepare($conn, "SELECT * FROM BloodStock WHERE BloodGroup=?");
        mysqli_stmt_bind_param($stockQuery, "s", $bloodGroup);
        mysqli_stmt_execute($stockQuery);
        $stockResult = mysqli_stmt_get_result($stockQuery);
        $stock = mysqli_fetch_assoc($stockResult);

        if ($stock && (int)$stock['UnitsAvailable'] >= $unitsRequested) {
            $newUnits = (int)$stock['UnitsAvailable'] - $unitsRequested;

            $stmt1 = mysqli_prepare($conn, "UPDATE BloodStock SET UnitsAvailable=? WHERE BloodGroup=?");
            mysqli_stmt_bind_param($stmt1, "is", $newUnits, $bloodGroup);
            mysqli_stmt_execute($stmt1);

            mysqli_query($conn, "UPDATE BloodRequest SET Status='Approved' WHERE RequestID=$requestID");
        } else {
            mysqli_query($conn, "UPDATE BloodRequest SET Status='Rejected' WHERE RequestID=$requestID");
        }
    }
}
header("Location: manage_requests.php");
exit();
?>
