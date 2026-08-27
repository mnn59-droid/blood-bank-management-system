<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['admin_email'])) { header("Location: ../login.php"); exit(); }

if (isset($_POST['save_stock'])) {
    $bloodgroup = trim($_POST['bloodgroup']);
    $units = (int)$_POST['units'];

    if (empty($bloodgroup) || $units < 0) {
        $msg = "Enter valid stock details.";
    } else {
        $check = mysqli_prepare($conn, "SELECT StockID FROM BloodStock WHERE BloodGroup=?");
        mysqli_stmt_bind_param($check, "s", $bloodgroup);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result) > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE BloodStock SET UnitsAvailable = UnitsAvailable + ? WHERE BloodGroup=?");
            mysqli_stmt_bind_param($stmt, "is", $units, $bloodgroup);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO BloodStock (BloodGroup, UnitsAvailable) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "si", $bloodgroup, $units);
        }

        $msg = mysqli_stmt_execute($stmt) ? "Blood stock updated successfully." : "Failed to update stock.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Stock</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/admin_top.php"); ?>
<?php include("../includes/admin_sidebar.php"); ?>
<div class="main">
    <div class="panel">
        <h1>Add Blood Stock</h1>
        <?php if (isset($msg)) echo "<p>" . e($msg) . "</p>"; ?>
        <form method="POST">
            <select name="bloodgroup">
                <option value="">Select Blood Group</option>
                <option value="A+">A+</option><option value="A-">A-</option>
                <option value="B+">B+</option><option value="B-">B-</option>
                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                <option value="O+">O+</option><option value="O-">O-</option>
            </select>
            <input type="number" name="units" min="0" placeholder="Units">
            <button type="submit" name="save_stock">Save Stock</button>
        </form>
    </div>
</div>
</body>
</html>