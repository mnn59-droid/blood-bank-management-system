<?php
include(__DIR__ . "/../config.php");
include(__DIR__ . "/response.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
    $method = 'PUT';
}

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        $stmt = mysqli_prepare($conn, "
            SELECT DonorID, FullName, Gender, DOB, BloodGroup, Phone, Email, Address, Status
            FROM Donor
            WHERE DonorID=?
        ");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $donor = mysqli_fetch_assoc($result);

        if ($donor) {
            jsonResponse(true, "Donor found", $donor);
        } else {
            jsonResponse(false, "Donor not found", null, 404);
        }
    }

    $search = trim($_GET['q'] ?? '');

    if ($search !== '') {
        $stmt = mysqli_prepare($conn, "
            SELECT Donor.*, COUNT(Donation.DonationID) AS total_donations
            FROM Donor
            LEFT JOIN Donation ON Donor.DonorID = Donation.DonorID
            WHERE Donor.FullName LIKE CONCAT('%', ?, '%')
               OR Donor.BloodGroup LIKE CONCAT('%', ?, '%')
               OR Donor.Phone LIKE CONCAT('%', ?, '%')
               OR Donor.Email LIKE CONCAT('%', ?, '%')
            GROUP BY Donor.DonorID
            ORDER BY Donor.DonorID DESC
        ");
        mysqli_stmt_bind_param($stmt, "ssss", $search, $search, $search, $search);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $donors = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $donors[] = $row;
        }

        jsonResponse(true, "Search results", $donors);
    } else {
        $result = mysqli_query($conn, "
            SELECT Donor.*, COUNT(Donation.DonationID) AS total_donations
            FROM Donor
            LEFT JOIN Donation ON Donor.DonorID = Donation.DonorID
            GROUP BY Donor.DonorID
            ORDER BY Donor.DonorID DESC
        ");

        $donors = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $donors[] = $row;
        }

        jsonResponse(true, "Donors fetched successfully", $donors);
    }
}

if ($method === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $bloodgroup = trim($_POST['bloodgroup'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $passwordRaw = trim($_POST['password'] ?? '');
    $status = trim($_POST['status'] ?? 'Available');

    if ($fullname === '' || $gender === '' || $dob === '' || $bloodgroup === '' || $phone === '' || $email === '' || $passwordRaw === '') {
        jsonResponse(false, "Missing required fields", null, 400);
    }

    $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "
        INSERT INTO Donor (FullName, Gender, DOB, BloodGroup, Phone, Email, Address, Password, Status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "sssssssss", $fullname, $gender, $dob, $bloodgroup, $phone, $email, $address, $password, $status);

    if (mysqli_stmt_execute($stmt)) {
        jsonResponse(true, "Donor created successfully", ['DonorID' => mysqli_insert_id($conn)], 201);
    } else {
        jsonResponse(false, "Failed to create donor", mysqli_error($conn), 500);
    }
}

if ($method === 'DELETE') {
    parse_str($_SERVER['QUERY_STRING'] ?? '', $query);
    $id = (int)($query['id'] ?? 0);

    if ($id <= 0) {
        jsonResponse(false, "Invalid donor ID", null, 400);
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM Donor WHERE DonorID=?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        jsonResponse(true, "Donor deleted successfully");
    } else {
        jsonResponse(false, "Failed to delete donor", mysqli_error($conn), 500);
    }
}

if ($method === 'PUT') {
    if (!empty($_POST)) {
        $data = $_POST;
    } else {
        parse_str(file_get_contents("php://input"), $data);
    }

    $id = (int)($data['id'] ?? 0);
    $fullname = trim($data['fullname'] ?? '');
    $gender = trim($data['gender'] ?? '');
    $dob = trim($data['dob'] ?? '');
    $bloodgroup = trim($data['bloodgroup'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $email = trim($data['email'] ?? '');
    $address = trim($data['address'] ?? '');
    $status = trim($data['status'] ?? '');
    $newPassword = trim($data['password'] ?? '');

    if ($id <= 0 || $fullname === '' || $gender === '' || $dob === '' || $bloodgroup === '' || $phone === '' || $email === '' || $status === '') {
        jsonResponse(false, "Missing required fields", null, 400);
    }

    if ($newPassword !== '') {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "
            UPDATE Donor
            SET FullName=?, Gender=?, DOB=?, BloodGroup=?, Phone=?, Email=?, Address=?, Status=?, Password=?
            WHERE DonorID=?
        ");
        mysqli_stmt_bind_param($stmt, "sssssssssi", $fullname, $gender, $dob, $bloodgroup, $phone, $email, $address, $status, $hashedPassword, $id);
    } else {
        $stmt = mysqli_prepare($conn, "
            UPDATE Donor
            SET FullName=?, Gender=?, DOB=?, BloodGroup=?, Phone=?, Email=?, Address=?, Status=?
            WHERE DonorID=?
        ");
        mysqli_stmt_bind_param($stmt, "ssssssssi", $fullname, $gender, $dob, $bloodgroup, $phone, $email, $address, $status, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        jsonResponse(true, "Donor updated successfully");
    } else {
        jsonResponse(false, "Failed to update donor", mysqli_error($conn), 500);
    }
}

jsonResponse(false, "Method not allowed", null, 405);
?>