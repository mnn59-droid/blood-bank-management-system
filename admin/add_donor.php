<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['admin_email'])) { 
    header("Location: ../login.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Donor</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include(__DIR__ . "/../includes/admin_top.php"); ?>
<?php include(__DIR__ . "/../includes/admin_sidebar.php"); ?>

<div class="main">
    <div class="panel">
        <h1>Add Donor</h1>

        <p id="messageBox"></p>

        <form id="donorForm">
            <input type="text" name="fullname" placeholder="Full Name" required>

            <select name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <input type="date" name="dob" required>

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

            <input type="text" name="phone" placeholder="Phone" required>
            <input type="email" name="email" placeholder="Email" required>
            <textarea name="address" placeholder="Address"></textarea>
            <input type="password" name="password" placeholder="Password" required>

            <select name="status" required>
                <option value="">Select Status</option>
                <option value="Available">Available</option>
                <option value="Not Available">Not Available</option>
            </select>

            <button type="submit">Save Donor</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {
    $('#donorForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: '../api/donors.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#messageBox').html('<span style="color:green;">' + res.message + '</span>');
                    $('#donorForm')[0].reset();
                } else {
                    $('#messageBox').html('<span style="color:red;">' + res.message + '</span>');
                }
            },
            error: function () {
                $('#messageBox').html('<span style="color:red;">Error saving donor.</span>');
            }
        });
    });
});
</script>

</body>
</html>