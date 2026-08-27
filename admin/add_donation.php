<?php
include(__DIR__ . "/../config.php");

if (!isset($_SESSION['admin_email'])) { 
    header("Location: ../login.php"); 
    exit(); 
}

$donors = mysqli_query($conn, "SELECT * FROM Donor ORDER BY FullName ASC");
$selectedDonorID = isset($_GET['donorID']) ? (int)$_GET['donorID'] : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Donation</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include(__DIR__ . "/../includes/admin_top.php"); ?>
<?php include(__DIR__ . "/../includes/admin_sidebar.php"); ?>

<div class="main">
    <div class="panel">
        <h1>Add Donation</h1>

        <p id="messageBox"></p>

        <form id="donationForm">
            <select name="donorID" id="donorID" required>
                <option value="">Select Donor</option>
                <?php while ($donor = mysqli_fetch_assoc($donors)) { ?>
                    <option 
                        value="<?php echo $donor['DonorID']; ?>"
                        data-bloodgroup="<?php echo e($donor['BloodGroup']); ?>"
                        <?php if ($donor['DonorID'] == $selectedDonorID) echo "selected"; ?>>
                        <?php echo e($donor['FullName']); ?>
                    </option>
                <?php } ?>
            </select>

            <select name="bloodgroup" id="bloodgroup" required>
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

            <input type="number" name="units" min="1" placeholder="Units Donated" required>
            <input type="date" name="donationdate" required>

            <button type="submit">Save Donation</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {

    function fillBloodGroupFromDonor() {
        const selectedOption = $('#donorID option:selected');
        const bloodGroup = selectedOption.data('bloodgroup') || '';

        if (bloodGroup) {
            $('#bloodgroup').val(bloodGroup);
        } else {
            $('#bloodgroup').val('');
        }
    }

    const donorSelected = $('#donorID').val();

    if (donorSelected) {
        $('#messageBox').html('<span style="color:#0275d8;">Donor pre-selected</span>');
        fillBloodGroupFromDonor();
    }

    $('#donorID').on('change', function () {
        fillBloodGroupFromDonor();
    });

    $('#donationForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: '../api/donations.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#messageBox').html('<span style="color:green;">' + res.message + '</span>');
                    $('#donationForm')[0].reset();
                } else {
                    $('#messageBox').html('<span style="color:red;">' + res.message + '</span>');
                }
            },
            error: function () {
                $('#messageBox').html('<span style="color:red;">Error saving donation.</span>');
            }
        });
    });
});
</script>

</body>
</html>