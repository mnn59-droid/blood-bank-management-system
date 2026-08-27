<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['admin_email'])) { header("Location: ../login.php"); exit(); }

$hospitals = mysqli_query($conn, "SELECT * FROM Hospital ORDER BY HospitalName ASC");

if (isset($_POST['save_request'])) {
    $hospitalID = (int)$_POST['hospitalID'];
    $bloodgroup = trim($_POST['bloodgroup']);
    $units = (int)$_POST['units'];
    $requestDate = $_POST['requestdate'];
    $status = trim($_POST['status']);

    if ($hospitalID <= 0 || empty($bloodgroup) || $units <= 0 || empty($requestDate) || empty($status)) {
        $msg = "Please fill in all required fields.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO BloodRequest (HospitalID, BloodGroup, UnitsRequested, RequestDate, Status) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isiss", $hospitalID, $bloodgroup, $units, $requestDate, $status);
        $msg = mysqli_stmt_execute($stmt) ? "Blood request added successfully." : "Failed to add request.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Request</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/admin_top.php"); ?>
<?php include("../includes/admin_sidebar.php"); ?>
<div class="main">
    <div class="panel">
        <h1>Blood Request Form</h1>
        <?php if (isset($msg)) echo "<p>" . e($msg) . "</p>"; ?>
        <form method="POST">
            <select name="hospitalID">
                <option value="">Select Hospital</option>
                <?php while ($hospital = mysqli_fetch_assoc($hospitals)) { ?>
                    <option value="<?php echo $hospital['HospitalID']; ?>"><?php echo e($hospital['HospitalName']); ?></option>
                <?php } ?>
            </select>
            <select name="bloodgroup">
                <option value="">Select Blood Group</option>
                <option value="A+">A+</option><option value="A-">A-</option>
                <option value="B+">B+</option><option value="B-">B-</option>
                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                <option value="O+">O+</option><option value="O-">O-</option>
            </select>
            <input type="number" name="units" min="1" placeholder="Units Requested">
            <input type="date" name="requestdate">
            <select name="status">
                <option value="">Select Status</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
            <button type="submit" name="save_request">Save Request</button>
        </form>
    </div>
</div>
</body>
</html>