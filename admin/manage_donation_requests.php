<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['admin_email'])) {
    header("Location: ../login.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Donation Requests</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include(__DIR__ . "/../includes/admin_top.php"); ?>
<?php include(__DIR__ . "/../includes/admin_sidebar.php"); ?>

<div class="main">
    <div class="panel">
        <h1>Manage Donation Requests</h1>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Donor</th>
                    <th>Blood Group</th>
                    <th>Units</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="donationRequestsBody">
                <tr>
                    <td colspan="7">Loading requests...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
const csrfToken = "<?php echo $_SESSION['csrf_token']; ?>";

function processDonationRequest(id, action, button) {
    $.ajax({
        url: 'process_donation_request.php',
        method: 'POST',
        dataType: 'json',
        data: {
            request_id: id,
            action: action,
            csrf_token: csrfToken
        },
        success: function(res) {
            if (res.success) {
                const tr = button.closest('tr');
                tr.find('.status-cell').text(res.status);

                if (res.status === 'Approved') {
                    tr.find('.status-cell').css({ color: 'green', fontWeight: 'bold' });
                } else if (res.status === 'Rejected') {
                    tr.find('.status-cell').css({ color: 'red', fontWeight: 'bold' });
                }

                tr.find('.action-cell').html('-');
            } else {
                alert(res.message || 'Failed to process request.');
            }
        },
        error: function() {
            alert('Error processing donation request.');
        }
    });
}

function loadDonationRequests() {
    $.ajax({
        url: '../api/donation_requests.php',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            if (!res.success || !res.data) {
                $('#donationRequestsBody').html('<tr><td colspan="7">Failed to load requests.</td></tr>');
                return;
            }

            let html = '';

            if (res.data.length === 0) {
                html = '<tr><td colspan="7">No donation requests found.</td></tr>';
            } else {
                res.data.forEach(row => {
                    let color = 'orange';
                    if (row.Status === 'Approved') color = 'green';
                    else if (row.Status === 'Rejected') color = 'red';

                    let actionHtml = '-';
                    if (row.Status === 'Pending') {
                        actionHtml = `
                            <button type="button" class="approve-btn action-btn" data-id="${row.RequestID}">Approve</button>
                            <button type="button" class="reject-btn action-btn" data-id="${row.RequestID}">Reject</button>
                        `;
                    }

                    html += `
                        <tr>
                            <td>${row.RequestID}</td>
                            <td>${row.FullName ? row.FullName : 'N/A'}</td>
                            <td>${row.BloodGroup}</td>
                            <td>${row.UnitsRequested}</td>
                            <td>${row.RequestDate}</td>
                            <td class="status-cell" style="color:${color}; font-weight:bold;">${row.Status}</td>
                            <td class="action-cell">${actionHtml}</td>
                        </tr>
                    `;
                });
            }

            $('#donationRequestsBody').html(html);
        },
        error: function() {
            $('#donationRequestsBody').html('<tr><td colspan="7">Error loading requests.</td></tr>');
        }
    });
}

$(document).ready(function () {
    loadDonationRequests();

    $(document).on('click', '.approve-btn', function() {
        processDonationRequest($(this).data('id'), 'approve', $(this));
    });

    $(document).on('click', '.reject-btn', function() {
        processDonationRequest($(this).data('id'), 'reject', $(this));
    });
});
</script>

</body>
</html>