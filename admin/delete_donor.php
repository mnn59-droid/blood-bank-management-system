

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

if (!isset($_POST['donor_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
    exit();
}

$donorID = (int)$_POST['donor_id'];

if ($donorID <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid donor ID"
    ]);
    exit();
}

mysqli_begin_transaction($conn);

try {
    $stmt1 = mysqli_prepare($conn, "DELETE FROM Donation WHERE DonorID = ?");
    mysqli_stmt_bind_param($stmt1, "i", $donorID);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_close($stmt1);

    $stmt2 = mysqli_prepare($conn, "DELETE FROM Donor WHERE DonorID = ?");
    mysqli_stmt_bind_param($stmt2, "i", $donorID);
    mysqli_stmt_execute($stmt2);

    if (mysqli_stmt_affected_rows($stmt2) > 0) {
        mysqli_commit($conn);

        echo json_encode([
            "success" => true,
            "message" => "Donor deleted successfully"
        ]);
    } else {
        mysqli_rollback($conn);

        echo json_encode([
            "success" => false,
            "message" => "Donor not found"
        ]);
    }

    mysqli_stmt_close($stmt2);

} catch (Throwable $e) {
    mysqli_rollback($conn);

    echo json_encode([
        "success" => false,
        "message" => "Server error"
    ]);
}
?>