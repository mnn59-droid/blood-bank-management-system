<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['donor_id'])) { header("Location: ../donor_login.php"); exit(); }

$donorID = $_SESSION['donor_id'];
$result = mysqli_query($conn, "SELECT * FROM Donation WHERE DonorID = $donorID ORDER BY DonationID DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Donation History</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/donor_top.php"); ?>
<?php include("../includes/donor_sidebar.php"); ?>
<div class="main">
    <div class="panel">
        <h1>My Donation History</h1>
        <table>
            <tr><th>ID</th><th>Blood Group</th><th>Units Donated</th><th>Donation Date</th></tr>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['DonationID']; ?></td>
                    <td><?php echo e($row['BloodGroup']); ?></td>
                    <td><?php echo $row['UnitsDonated']; ?></td>
                    <td><?php echo e($row['DonationDate']); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>