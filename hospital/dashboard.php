<?php
require_once(__DIR__ . "/../config.php");

if (!isset($_SESSION['hospital_id'])) { 
    header("Location: ../hospital_login.php"); 
    exit(); 
}

$hospitalID = (int)$_SESSION['hospital_id'];

$request_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM BloodRequest WHERE HospitalID = $hospitalID")
)['total'];

$pending_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM BloodRequest WHERE HospitalID = $hospitalID AND Status='Pending'")
)['total'];

$approved_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM BloodRequest WHERE HospitalID = $hospitalID AND Status='Approved'")
)['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hospital Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<?php include("../includes/hospital_top.php"); ?>
<?php include("../includes/hospital_sidebar.php"); ?>

<div class="main">

    <!-- HEADER PANEL -->
    <div class="panel" style="margin-bottom: 20px;">
        <h1 style="margin-bottom: 8px;">
            Welcome, <?php echo e($_SESSION['hospital_name']); ?>
        </h1>
        <p style="color:#6b7280; margin:0;">
            Manage blood requests, track approvals, and monitor activity.
        </p>
    </div>

    <!-- STATS -->
    <div class="cards">

        <a href="request_status.php" class="card-link">
            <div class="card">
                <h3>Total Requests</h3>
                <p><?php echo (int)$request_count; ?></p>
            </div>
        </a>

        <a href="request_status.php?status=Pending" class="card-link">
            <div class="card">
                <h3>Pending Requests</h3>
                <p><?php echo (int)$pending_count; ?></p>
            </div>
        </a>

        <a href="request_status.php?status=Approved" class="card-link">
            <div class="card">
                <h3>Approved Requests</h3>
                <p><?php echo (int)$approved_count; ?></p>
            </div>
        </a>

    </div>

    <!-- QUICK ACTIONS -->
    <div class="panel">
        <h2>Quick Actions</h2>

        <div class="cards">

            <a href="request_blood.php" class="card-link">
                <div class="card">
                    <h3>Request Blood</h3>
                    <p style="font-size:18px;">Create</p>
                </div>
            </a>

            <a href="request_status.php" class="card-link">
                <div class="card">
                    <h3>Track Requests</h3>
                    <p style="font-size:18px;">View</p>
                </div>
            </a>

            <a href="profile.php" class="card-link">
                <div class="card">
                    <h3>Hospital Profile</h3>
                    <p style="font-size:18px;">Manage</p>
                </div>
            </a>

        </div>
    </div>

</div>
</body>
</html>