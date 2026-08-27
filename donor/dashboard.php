<?php
require_once(__DIR__ . "/../config.php");
if (!isset($_SESSION['donor_id'])) { 
    header("Location: ../donor_login.php"); 
    exit(); 
}

$donorID = (int)$_SESSION['donor_id'];

$donor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Donor WHERE DonorID = $donorID"));
$donation_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Donation WHERE DonorID = $donorID"))['total'];
$pending_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM DonationRequest WHERE DonorID = $donorID AND Status = 'Pending'"))['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Donor Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include(__DIR__ . "/../includes/donor_top.php"); ?>
<?php include(__DIR__ . "/../includes/donor_sidebar.php"); ?>

<div class="main">
    <div class="panel" style="margin-bottom: 20px;">
        <h1 style="margin-bottom: 8px;">Welcome, <?php echo e($_SESSION['donor_name']); ?></h1>
        <p style="color:#6b7280; margin:0;">
            View your donation profile, latest activity, and request status.
        </p>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Blood Group</h3>
            <p><?php echo e($donor['BloodGroup']); ?></p>
        </div>

        <div class="card">
            <h3>Total Donations</h3>
            <p><?php echo (int)$donation_count; ?></p>
        </div>

        <div class="card">
            <h3>Last Donation</h3>
            <p><?php echo $donor['LastDonationDate'] ? e($donor['LastDonationDate']) : 'None'; ?></p>
        </div>

        <div class="card">
            <h3>Status</h3>
            <p><?php echo e($donor['Status']); ?></p>
        </div>

        <a href="request_status.php" class="card-link">
            <div class="card">
                <h3>Pending Requests</h3>
                <p><?php echo (int)$pending_requests; ?></p>
            </div>
        </a>
    </div>

    <div class="panel">
        <h2>Quick Actions</h2>
        <div class="cards">
            <a href="request_donation.php" class="card-link">
                <div class="card">
                    <h3>Request Donation</h3>
                    <p style="font-size:18px;">Open</p>
                </div>
            </a>

            <a href="request_status.php" class="card-link">
                <div class="card">
                    <h3>Request Status</h3>
                    <p style="font-size:18px;">Track</p>
                </div>
            </a>

            <a href="donation_history.php" class="card-link">
                <div class="card">
                    <h3>Donation History</h3>
                    <p style="font-size:18px;">View</p>
                </div>
            </a>

            <a href="profile.php" class="card-link">
                <div class="card">
                    <h3>My Profile</h3>
                    <p style="font-size:18px;">Manage</p>
                </div>
            </a>
        </div>
    </div>
</div>
</body>
</html>