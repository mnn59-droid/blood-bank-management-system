<?php
include(__DIR__ . "/../config.php");
include(__DIR__ . "/response.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "
        SELECT Donation.*, Donor.FullName
        FROM Donation
        INNER JOIN Donor ON Donation.DonorID = Donor.DonorID
        ORDER BY Donation.DonationID DESC
    ";
    $result = mysqli_query($conn, $sql);

    $donations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $donations[] = $row;
    }

    jsonResponse(true, "Donations fetched successfully", $donations);
}

if ($method === 'POST') {
    $donorID = (int)($_POST['donorID'] ?? 0);
    $bloodgroup = trim($_POST['bloodgroup'] ?? '');
    $units = (int)($_POST['units'] ?? 0);
    $donationDate = trim($_POST['donationdate'] ?? '');

    if ($donorID <= 0 || $bloodgroup === '' || $units <= 0 || $donationDate === '') {
        jsonResponse(false, "Missing required fields", null, 400);
    }

    mysqli_begin_transaction($conn);

    try {
        $stmt = mysqli_prepare($conn, "INSERT INTO Donation (DonorID, BloodGroup, UnitsDonated, DonationDate) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isis", $donorID, $bloodgroup, $units, $donationDate);
        mysqli_stmt_execute($stmt);

        $check = mysqli_prepare($conn, "SELECT StockID, UnitsAvailable FROM BloodStock WHERE BloodGroup=?");
        mysqli_stmt_bind_param($check, "s", $bloodgroup);
        mysqli_stmt_execute($check);
        $res = mysqli_stmt_get_result($check);
        $stock = mysqli_fetch_assoc($res);

        if ($stock) {
            $newUnits = (int)$stock['UnitsAvailable'] + $units;
            $up = mysqli_prepare($conn, "UPDATE BloodStock SET UnitsAvailable=? WHERE BloodGroup=?");
            mysqli_stmt_bind_param($up, "is", $newUnits, $bloodgroup);
            mysqli_stmt_execute($up);
        } else {
            $ins = mysqli_prepare($conn, "INSERT INTO BloodStock (BloodGroup, UnitsAvailable) VALUES (?, ?)");
            mysqli_stmt_bind_param($ins, "si", $bloodgroup, $units);
            mysqli_stmt_execute($ins);
        }

        $upd = mysqli_prepare($conn, "UPDATE Donor SET LastDonationDate=? WHERE DonorID=?");
        mysqli_stmt_bind_param($upd, "si", $donationDate, $donorID);
        mysqli_stmt_execute($upd);

        mysqli_commit($conn);
        jsonResponse(true, "Donation created and stock updated");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        jsonResponse(false, "Failed to save donation", $e->getMessage(), 500);
    }
}

jsonResponse(false, "Method not allowed", null, 405);
?>