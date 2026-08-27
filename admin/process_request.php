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
    $reqStmt = mysqli_prepare(
        $conn,
        "SELECT RequestID, BloodGroup, UnitsRequested, Status
         FROM BloodRequest
         WHERE RequestID = ?
         FOR UPDATE"
    );
    mysqli_stmt_bind_param($reqStmt, "i", $requestID);
    mysqli_stmt_execute($reqStmt);

    $reqResult = mysqli_stmt_get_result($reqStmt);
    $request = mysqli_fetch_assoc($reqResult);
    mysqli_stmt_close($reqStmt);

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

    $bloodGroup = $request['BloodGroup'];
    $unitsRequested = (int)$request['UnitsRequested'];

    if ($action === 'approve') {
        $stockStmt = mysqli_prepare(
            $conn,
            "UPDATE BloodStock
             SET UnitsAvailable = UnitsAvailable - ?
             WHERE BloodGroup = ? AND UnitsAvailable >= ?"
        );
        mysqli_stmt_bind_param($stockStmt, "isi", $unitsRequested, $bloodGroup, $unitsRequested);
        mysqli_stmt_execute($stockStmt);

        $updated = mysqli_stmt_affected_rows($stockStmt);
        mysqli_stmt_close($stockStmt);

        if ($updated > 0) {
            $approveStmt = mysqli_prepare(
                $conn,
                "UPDATE BloodRequest
                 SET Status = 'Approved'
                 WHERE RequestID = ? AND Status = 'Pending'"
            );
            mysqli_stmt_bind_param($approveStmt, "i", $requestID);
            mysqli_stmt_execute($approveStmt);
            mysqli_stmt_close($approveStmt);

            mysqli_commit($conn);

            echo json_encode([
                "success" => true,
                "status" => "Approved",
                "message" => "Request approved successfully"
            ]);
            exit();
        } else {
            $rejectStmt = mysqli_prepare(
                $conn,
                "UPDATE BloodRequest
                 SET Status = 'Rejected'
                 WHERE RequestID = ? AND Status = 'Pending'"
            );
            mysqli_stmt_bind_param($rejectStmt, "i", $requestID);
            mysqli_stmt_execute($rejectStmt);
            mysqli_stmt_close($rejectStmt);

            mysqli_commit($conn);

            echo json_encode([
                "success" => true,
                "status" => "Rejected",
                "message" => "Not enough stock. Request rejected."
            ]);
            exit();
        }
    }

    if ($action === 'reject') {
        $rejectStmt = mysqli_prepare(
            $conn,
            "UPDATE BloodRequest
             SET Status = 'Rejected'
             WHERE RequestID = ? AND Status = 'Pending'"
        );
        mysqli_stmt_bind_param($rejectStmt, "i", $requestID);
        mysqli_stmt_execute($rejectStmt);
        mysqli_stmt_close($rejectStmt);

        mysqli_commit($conn);

        echo json_encode([
            "success" => true,
            "status" => "Rejected",
            "message" => "Request rejected successfully"
        ]);
        exit();
    }

} catch (Throwable $e) {
    mysqli_rollback($conn);

    echo json_encode([
        "success" => false,
        "message" => "Server error"
    ]);
    exit();
}
?>