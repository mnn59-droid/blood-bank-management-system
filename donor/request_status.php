<?php
include(__DIR__ . "/../config.php");

if (!isset($_SESSION['donor_id'])) {
    header("Location: ../donor_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Donation Requests</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include(__DIR__ . "/../includes/donor_top.php"); ?>
<?php include(__DIR__ . "/../includes/donor_sidebar.php"); ?>

<div class="main">
    <div class="panel">
        <h1>My Donation Requests</h1>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Blood Group</th>
                    <th>Units</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="requestStatusBody">
                <tr>
                    <td colspan="5">Loading requests...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {
    $.ajax({
        url: '../api/donation_requests.php',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            if (!res.success || !res.data) {
                $('#requestStatusBody').html('<tr><td colspan="5">Failed to load requests.</td></tr>');
                return;
            }

            let html = '';

            if (res.data.length === 0) {
                html = '<tr><td colspan="5">No donation requests found.</td></tr>';
            } else {
                res.data.forEach(row => {
                    let color = 'orange';
                    if (row.Status === 'Approved') color = 'green';
                    else if (row.Status === 'Rejected') color = 'red';

                    html += `
                        <tr>
                            <td>${row.RequestID}</td>
                            <td>${row.BloodGroup}</td>
                            <td>${row.UnitsRequested}</td>
                            <td>${row.RequestDate}</td>
                            <td style="color:${color}; font-weight:bold;">${row.Status}</td>
                        </tr>
                    `;
                });
            }

            $('#requestStatusBody').html(html);
        },
        error: function() {
            $('#requestStatusBody').html('<tr><td colspan="5">Error loading requests.</td></tr>');
        }
    });
});
</script>

</body>
</html>