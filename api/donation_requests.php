<?php
include(__DIR__ . "/../config.php");
include(__DIR__ . "/response.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {

    if (!isset($_SESSION['admin_email']) && !isset($_SESSION['donor_id'])) {
        jsonResponse(false, "Unauthorized", null, 401);
    }

    // ADMIN VIEW FIRST
    if (isset($_SESSION['admin_email'])) {

        $result = mysqli_query($conn, "
            SELECT 
                DonationRequest.RequestID,
                DonationRequest.DonorID,
                DonationRequest.BloodGroup,
                DonationRequest.UnitsRequested,
                DonationRequest.RequestDate,
                DonationRequest.Status,
                DonationRequest.CreatedAt,
                Donor.FullName
            FROM DonationRequest
            INNER JOIN Donor ON DonationRequest.DonorID = Donor.DonorID
            ORDER BY DonationRequest.RequestID DESC
        ");

        $requests = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $requests[] = $row;
        }

        jsonResponse(true, "Donation requests fetched successfully", $requests);
    }

    // DONOR VIEW
    if (isset($_SESSION['donor_id'])) {
        $donorID = (int)$_SESSION['donor_id'];

        $stmt = mysqli_prepare($conn, "
            SELECT RequestID, DonorID, BloodGroup, UnitsRequested, RequestDate, Status, CreatedAt
            FROM DonationRequest
            WHERE DonorID = ?
            ORDER BY RequestID DESC
        ");
        mysqli_stmt_bind_param($stmt, "i", $donorID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $requests = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $requests[] = $row;
        }

        jsonResponse(true, "Donation requests fetched successfully", $requests);
    }
}

if ($method === 'POST') {
    if (!isset($_SESSION['donor_id'])) {
        jsonResponse(false, "Unauthorized", null, 401);
    }

    $donorID = (int)$_SESSION['donor_id'];
    $bloodgroup = trim($_POST['bloodgroup'] ?? '');
    $units = (int)($_POST['units'] ?? 0);
    $requestDate = trim($_POST['requestdate'] ?? '');
    $status = 'Pending';

    $allowedGroups = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];

    if ($bloodgroup === '' || $units <= 0 || $requestDate === '') {
        jsonResponse(false, "Missing required fields", null, 400);
    }

    if (!in_array($bloodgroup, $allowedGroups, true)) {
        jsonResponse(false, "Invalid blood group", null, 400);
    }

    $stmt = mysqli_prepare($conn, "
        INSERT INTO DonationRequest (DonorID, BloodGroup, UnitsRequested, RequestDate, Status)
        VALUES (?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "isiss", $donorID, $bloodgroup, $units, $requestDate, $status);

    if (mysqli_stmt_execute($stmt)) {
        jsonResponse(true, "Donation request submitted successfully", ['RequestID' => mysqli_insert_id($conn)], 201);
    } else {
        error_log(mysqli_error($conn));
        jsonResponse(false, "Failed to submit donation request", null, 500);
    }
}

jsonResponse(false, "Method not allowed", null, 405);
?>