<?php
include(__DIR__ . "/../config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['admin_email'])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit();
}

if (
    !isset($_POST['request_id'], $_POST['action'], $_POST['csrf_token']) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
    exit();
}

$requestID = (int)$_POST['request_id'];
$action = $_POST['action'];

if (!in_array($action, ['approve', 'reject'], true)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid action"
    ]);
    exit();
}

mysqli_begin_transaction($conn);

try {
    $stmt = mysqli_prepare($conn, "
        SELECT RequestID, DonorID, BloodGroup, UnitsRequested, RequestDate, Status
        FROM DonationRequest
        WHERE RequestID = ?
        FOR UPDATE
    ");
    mysqli_stmt_bind_param($stmt, "i", $requestID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $request = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$request) {
        mysqli_rollback($conn);
        echo json_encode([
            "success" => false,
            "message" => "Request not found"
        ]);
        exit();
    }

    if ($request['Status'] !== 'Pending') {
        mysqli_commit($conn);
        echo json_encode([
            "success" => false,
            "message" => "Request already processed"
        ]);
        exit();
    }

    if ($action === 'approve') {
        $insertDonation = mysqli_prepare($conn, "
            INSERT INTO Donation (DonorID, BloodGroup, UnitsDonated, DonationDate)
            VALUES (?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param(
            $insertDonation,
            "isis",
            $request['DonorID'],
            $request['BloodGroup'],
            $request['UnitsRequested'],
            $request['RequestDate']
        );
        mysqli_stmt_execute($insertDonation);
        mysqli_stmt_close($insertDonation);

        $stockCheck = mysqli_prepare($conn, "
            SELECT StockID, UnitsAvailable
            FROM BloodStock
            WHERE BloodGroup = ?
            FOR UPDATE
        ");
        mysqli_stmt_bind_param($stockCheck, "s", $request['BloodGroup']);
        mysqli_stmt_execute($stockCheck);
        $stockResult = mysqli_stmt_get_result($stockCheck);
        $stock = mysqli_fetch_assoc($stockResult);
        mysqli_stmt_close($stockCheck);

        if ($stock) {
            $newUnits = (int)$stock['UnitsAvailable'] + (int)$request['UnitsRequested'];

            $updateStock = mysqli_prepare($conn, "
                UPDATE BloodStock
                SET UnitsAvailable = ?
                WHERE BloodGroup = ?
            ");
            mysqli_stmt_bind_param($updateStock, "is", $newUnits, $request['BloodGroup']);
            mysqli_stmt_execute($updateStock);
            mysqli_stmt_close($updateStock);
        } else {
            $insertStock = mysqli_prepare($conn, "
                INSERT INTO BloodStock (BloodGroup, UnitsAvailable)
                VALUES (?, ?)
            ");
            mysqli_stmt_bind_param($insertStock, "si", $request['BloodGroup'], $request['UnitsRequested']);
            mysqli_stmt_execute($insertStock);
            mysqli_stmt_close($insertStock);
        }

        $updateDonor = mysqli_prepare($conn, "
            UPDATE Donor
            SET LastDonationDate = ?
            WHERE DonorID = ?
        ");
        mysqli_stmt_bind_param($updateDonor, "si", $request['RequestDate'], $request['DonorID']);
        mysqli_stmt_execute($updateDonor);
        mysqli_stmt_close($updateDonor);

        $updateRequest = mysqli_prepare($conn, "
            UPDATE DonationRequest
            SET Status = 'Approved'
            WHERE RequestID = ?
        ");
        mysqli_stmt_bind_param($updateRequest, "i", $requestID);
        mysqli_stmt_execute($updateRequest);
        mysqli_stmt_close($updateRequest);

        mysqli_commit($conn);

        echo json_encode([
            "success" => true,
            "status" => "Approved",
            "message" => "Donation request approved successfully"
        ]);
        exit();
    }

    if ($action === 'reject') {
        $rejectStmt = mysqli_prepare($conn, "
            UPDATE DonationRequest
            SET Status = 'Rejected'
            WHERE RequestID = ?
        ");
        mysqli_stmt_bind_param($rejectStmt, "i", $requestID);
        mysqli_stmt_execute($rejectStmt);
        mysqli_stmt_close($rejectStmt);

        mysqli_commit($conn);

        echo json_encode([
            "success" => true,
            "status" => "Rejected",
            "message" => "Donation request rejected successfully"
        ]);
        exit();
    }

} catch (Throwable $e) {
    mysqli_rollback($conn);
    error_log($e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "Server error"
    ]);
    exit();
}
?>