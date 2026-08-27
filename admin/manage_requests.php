<?php 
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['admin_email'])) { header("Location: ../login.php"); exit(); }

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Requests</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include(__DIR__ . "/../includes/admin_top.php"); ?>
<?php include(__DIR__ . "/../includes/admin_sidebar.php"); ?>

<div class="main">
    <div class="panel">
        <h1>Manage Blood Requests</h1>

        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hospital</th>
                    <th>Blood Group</th>
                    <th>Units</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="requestsTableBody">
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

function processRequest(id, action, button) {
    $.ajax({
        url: 'process_request.php',
        method: 'POST',
        dataType: 'json',
        data: {
            request_id: id,
            action: action,
            csrf_token: csrfToken
        },
        success: function(res) {
            if (res.success) {
                const row = button.closest('tr');
                row.find('.status-cell').text(res.status);

                if (res.status === 'Approved') {
                    row.find('.status-cell').css({
                        color: 'green',
                        fontWeight: 'bold'
                    });
                } else if (res.status === 'Rejected') {
                    row.find('.status-cell').css({
                        color: 'red',
                        fontWeight: 'bold'
                    });
                }

                row.find('.action-cell').html('-');
            } else {
                alert(res.message || 'Failed to process request.');
            }
        },
        error: function() {
            alert('Error processing request.');
        }
    });
}

function loadRequests() {
    $.ajax({
        url: '../api/requests.php',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            if (!res.success || !res.data) {
                $('#requestsTableBody').html('<tr><td colspan="7">Failed to load requests.</td></tr>');
                return;
            }

            let html = '';

            if (res.data.length === 0) {
                html = '<tr><td colspan="7">No blood requests found.</td></tr>';
            } else {
                res.data.forEach(row => {
                    let actionHtml = '-';
                    let statusStyle = '';

                    if (row.Status === 'Approved') {
                        statusStyle = 'style="color:green;font-weight:bold;"';
                    } else if (row.Status === 'Rejected') {
                        statusStyle = 'style="color:red;font-weight:bold;"';
                    } else if (row.Status === 'Pending') {
                        statusStyle = 'style="color:orange;font-weight:bold;"';
                        actionHtml = `
                            <button type="button" class="approve-btn action-btn" data-id="${row.RequestID}">Approve</button>
                            <button type="button" class="delete-btn action-btn" data-id="${row.RequestID}">Reject</button>
                        `;
                    }

                    html += `
                        <tr>
                            <td>${row.RequestID}</td>
                            <td>${row.HospitalName ?? row.HospitalID}</td>
                            <td>${row.BloodGroup}</td>
                            <td>${row.UnitsRequested}</td>
                            <td>${row.RequestDate}</td>
                            <td class="status-cell" ${statusStyle}>${row.Status}</td>
                            <td class="action-cell">${actionHtml}</td>
                        </tr>
                    `;
                });
            }

            $('#requestsTableBody').html(html);
        },
        error: function() {
            $('#requestsTableBody').html('<tr><td colspan="7">Error loading requests.</td></tr>');
        }
    });
}

$(document).ready(function() {
    loadRequests();

    $(document).on('click', '.approve-btn', function() {
        const button = $(this);
        const id = button.data('id');
        processRequest(id, 'approve', button);
    });

    $(document).on('click', '.delete-btn', function() {
        const button = $(this);
        const id = button.data('id');
        processRequest(id, 'reject', button);
    });
});
</script>

</body>
</html>