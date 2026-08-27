<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['hospital_id'])) { header("Location: ../hospital_login.php"); exit(); }

$hospitalID = (int)$_SESSION['hospital_id'];
$status = $_GET['status'] ?? '';

if ($status === 'Pending' || $status === 'Approved' || $status === 'Rejected') {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM BloodRequest WHERE HospitalID = ? AND Status = ? ORDER BY RequestID DESC"
    );
    mysqli_stmt_bind_param($stmt, "is", $hospitalID, $status);
} else {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM BloodRequest WHERE HospitalID = ? ORDER BY RequestID DESC"
    );
    mysqli_stmt_bind_param($stmt, "i", $hospitalID);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Request Status</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/hospital_top.php"); ?>
<?php include("../includes/hospital_sidebar.php"); ?>

<div class="main">
    <div class="panel">
        <h1>
            <?php
            if ($status === 'Pending' || $status === 'Approved' || $status === 'Rejected') {
                echo e($status) . " Blood Requests";
            } else {
                echo "My Blood Requests";
            }
            ?>
        </h1>

        <table>
            <tr>
                <th>ID</th>
                <th>Blood Group</th>
                <th>Units Requested</th>
                <th>Request Date</th>
                <th>Status</th>
            </tr>

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['RequestID']; ?></td>
                    <td><?php echo e($row['BloodGroup']); ?></td>
                    <td><?php echo $row['UnitsRequested']; ?></td>
                    <td><?php echo e($row['RequestDate']); ?></td>
                    <td><?php echo e($row['Status']); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>