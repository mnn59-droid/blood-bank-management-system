<?php
include(__DIR__ . "/../config.php");

if (!isset($_SESSION['donor_id'])) {
    header("Location: ../donor_login.php");
    exit();
}

$donorID = (int)$_SESSION['donor_id'];

$stmt = mysqli_prepare($conn, "SELECT FullName, BloodGroup, Status, LastDonationDate FROM Donor WHERE DonorID=?");
mysqli_stmt_bind_param($stmt, "i", $donorID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$donor = mysqli_fetch_assoc($result);

if (!$donor) {
    header("Location: ../donor_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Request Donation</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include(__DIR__ . "/../includes/donor_top.php"); ?>
<?php include(__DIR__ . "/../includes/donor_sidebar.php"); ?>

<div class="main">
    <div class="panel">
        <h1>Request Donation</h1>

        <p id="msg"></p>

        <form id="donationRequestForm">
            <input type="text" value="<?php echo e($donor['FullName']); ?>" readonly>
            <input type="text" id="bloodgroup" value="<?php echo e($donor['BloodGroup']); ?>" readonly>

            <input type="number" name="units" min="1" placeholder="Units Requested" required>
            <input type="date" name="requestdate" required>

            <button type="submit">Submit Request</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {
    $('#donationRequestForm').on('submit', function (e) {
        e.preventDefault();

        const formData = {
            bloodgroup: $('#bloodgroup').val(),
            units: $('input[name="units"]').val(),
            requestdate: $('input[name="requestdate"]').val()
        };

        $.ajax({
            url: '../api/donation_requests.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#msg').html('<p style="color:green;">' + res.message + '</p>');
                    $('#donationRequestForm')[0].reset();
                    $('#bloodgroup').val('<?php echo e($donor['BloodGroup']); ?>');
                } else {
                    $('#msg').html('<p style="color:red;">' + res.message + '</p>');
                }
            },
            error: function () {
                $('#msg').html('<p style="color:red;">Error submitting request.</p>');
            }
        });
    });
});
</script>

</body>
</html>