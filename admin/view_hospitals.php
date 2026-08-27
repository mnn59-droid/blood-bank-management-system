<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['admin_email'])) { header("Location: ../login.php"); exit(); }
$result = mysqli_query($conn, "SELECT * FROM Hospital ORDER BY HospitalID DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hospitals</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/admin_top.php"); ?>
<?php include("../includes/admin_sidebar.php"); ?>
<div class="main">
    <div class="panel">
        <h1>Hospitals</h1>
        <table>
            <tr><th>ID</th><th>Name</th><th>Contact</th><th>Phone</th><th>Email</th><th>Address</th></tr>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['HospitalID']; ?></td>
                    <td><?php echo e($row['HospitalName']); ?></td>
                    <td><?php echo e($row['ContactPerson']); ?></td>
                    <td><?php echo e($row['Phone']); ?></td>
                    <td><?php echo e($row['Email']); ?></td>
                    <td><?php echo e($row['Address']); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>