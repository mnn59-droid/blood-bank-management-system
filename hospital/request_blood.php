<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['hospital_id'])) { 
    header("Location: ../hospital_login.php"); 
    exit(); 
}

$hospitalID = (int)$_SESSION['hospital_id'];

if (isset($_POST['save_request'])) {
    $bloodgroup = trim($_POST['bloodgroup']);
    $units = (int)$_POST['units'];
    $requestDate = $_POST['requestdate'];
    $status = "Pending";

    if (empty($bloodgroup) || $units <= 0 || empty($requestDate)) {
        $msg = "Please fill in all required fields.";
    } else {
        $requestToken = bin2hex(random_bytes(16));

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO BloodRequest 
            (HospitalID, BloodGroup, UnitsRequested, RequestDate, Status, RequestToken) 
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "isisss",
            $hospitalID,
            $bloodgroup,
            $units,
            $requestDate,
            $status,
            $requestToken
        );

        if (mysqli_stmt_execute($stmt)) {
            $msg = "Blood request submitted successfully.";
        } else {
            if (mysqli_errno($conn) == 1062) {
                $msg = "Duplicate request detected.";
            } else {
                $msg = "Failed to submit request.";
            }
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Request Blood</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/hospital_top.php"); ?>
<?php include("../includes/hospital_sidebar.php"); ?>

<div class="main">
    <div class="panel">
        <h1>Request Blood</h1>

        <?php if (isset($msg)) echo "<p>" . e($msg) . "</p>"; ?>

        <form method="POST">
            <select name="bloodgroup" required>
                <option value="">Select Blood Group</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>

            <input type="number" name="units" min="1" placeholder="Units Requested" required>
            <input type="date" name="requestdate" required>
            <button type="submit" name="save_request">Submit Request</button>
        </form>
    </div>
</div>
</body>
</html>