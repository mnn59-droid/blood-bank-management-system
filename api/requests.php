<?php
include(__DIR__ . "/../config.php");
include(__DIR__ . "/response.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT BloodRequest.*, Hospital.HospitalName
            FROM BloodRequest
            INNER JOIN Hospital ON BloodRequest.HospitalID = Hospital.HospitalID
            ORDER BY BloodRequest.RequestID DESC";
    $result = mysqli_query($conn, $sql);

    $requests = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = $row;
    }

    jsonResponse(true, "Requests fetched successfully", $requests);
}

if ($method === 'POST') {
    $hospitalID = (int)($_POST['hospitalID'] ?? 0);
    $bloodgroup = trim($_POST['bloodgroup'] ?? '');
    $units = (int)($_POST['units'] ?? 0);
    $requestDate = trim($_POST['requestdate'] ?? '');
    $status = "Pending";

    if ($hospitalID <= 0 || $bloodgroup === '' || $units <= 0 || $requestDate === '') {
        jsonResponse(false, "Missing required fields", null, 400);
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO BloodRequest (HospitalID, BloodGroup, UnitsRequested, RequestDate, Status) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isiss", $hospitalID, $bloodgroup, $units, $requestDate, $status);

    if (mysqli_stmt_execute($stmt)) {
        jsonResponse(true, "Blood request created successfully", ['RequestID' => mysqli_insert_id($conn)], 201);
    } else {
        jsonResponse(false, "Failed to create request", mysqli_error($conn), 500);
    }
}

jsonResponse(false, "Method not allowed", null, 405);
?>