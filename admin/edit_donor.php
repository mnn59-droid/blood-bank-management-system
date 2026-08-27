<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['admin_email'])) { header("Location: ../login.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: view_donors.php"); exit(); }

$donorID = (int)$_GET['id'];
$res = mysqli_query($conn, "SELECT * FROM Donor WHERE DonorID = $donorID");
$donor = mysqli_fetch_assoc($res);

if (!$donor) { 
    header("Location: view_donors.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Donor</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include(__DIR__ . "/../includes/admin_top.php"); ?>
<?php include(__DIR__ . "/../includes/admin_sidebar.php"); ?>

<div class="main">
    <div class="panel">
        <h1>Edit Donor</h1>

        <p id="messageBox"></p>

        <form id="editDonorForm">
            <input type="hidden" name="id" value="<?php echo $donorID; ?>">

            <input type="text" name="fullname" value="<?php echo e($donor['FullName']); ?>" required>

            <select name="gender" required>
                <option value="Male" <?php if ($donor['Gender']=="Male") echo "selected"; ?>>Male</option>
                <option value="Female" <?php if ($donor['Gender']=="Female") echo "selected"; ?>>Female</option>
            </select>

            <input type="date" name="dob" value="<?php echo e($donor['DOB']); ?>" required>

            <select name="bloodgroup" required>
                <?php foreach (["A+","A-","B+","B-","AB+","AB-","O+","O-"] as $g) { ?>
                    <option value="<?php echo $g; ?>" <?php if ($donor['BloodGroup']==$g) echo "selected"; ?>><?php echo $g; ?></option>
                <?php } ?>
            </select>

            <input type="text" name="phone" value="<?php echo e($donor['Phone']); ?>" required>
            <input type="email" name="email" value="<?php echo e($donor['Email']); ?>" required>
            <textarea name="address"><?php echo e($donor['Address']); ?></textarea>
            <input type="password" name="password" placeholder="New password only if changing">

            <select name="status" required>
                <option value="Available" <?php if ($donor['Status']=="Available") echo "selected"; ?>>Available</option>
                <option value="Not Available" <?php if ($donor['Status']=="Not Available") echo "selected"; ?>>Not Available</option>
            </select>

            <button type="submit">Update Donor</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {
    $('#editDonorForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: '../api/donors.php',
            method: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#messageBox').html('<span style="color:green;">' + res.message + '</span>');
                    setTimeout(function () {
                        window.location.href = 'view_donors.php';
                    }, 800);
                } else {
                    $('#messageBox').html('<span style="color:red;">' + res.message + '</span>');
                }
            },
            error: function () {
                $('#messageBox').html('<span style="color:red;">Error updating donor.</span>');
            }
        });
    });
});
</script>

</body>
</html>