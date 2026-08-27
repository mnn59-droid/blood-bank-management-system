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
    <title>View Donations</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include(__DIR__ . "/../includes/admin_top.php"); ?>
<?php include(__DIR__ . "/../includes/admin_sidebar.php"); ?>

<div class="main">
    <div class="panel">
        <h1>Donation Records</h1>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Donor</th>
                    <th>Blood Group</th>
                    <th>Units</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody id="donationsTableBody">
                <tr>
                    <td colspan="5">Loading donations...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function loadDonations() {
    $.ajax({
        url: '../api/donations.php',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            if (!res.success || !res.data) {
                $('#donationsTableBody').html('<tr><td colspan="5">Failed to load donations.</td></tr>');
                return;
            }

            let html = '';

            if (res.data.length === 0) {
                html = '<tr><td colspan="5">No donation records found.</td></tr>';
            } else {
                res.data.forEach(row => {
                    html += `
                        <tr>
                            <td>${row.DonationID}</td>
                            <td>${row.FullName ?? ''}</td>
                            <td>${row.BloodGroup}</td>
                            <td>${row.UnitsDonated}</td>
                            <td>${row.DonationDate}</td>
                        </tr>
                    `;
                });
            }

            $('#donationsTableBody').html(html);
        },
        error: function() {
            $('#donationsTableBody').html('<tr><td colspan="5">Error loading donations.</td></tr>');
        }
    });
}

$(document).ready(function() {
    loadDonations();
});
</script>
</body>
</html>