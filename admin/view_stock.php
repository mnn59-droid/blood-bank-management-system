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
    <title>Blood Stock</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include(__DIR__ . "/../includes/admin_top.php"); ?>
<?php include(__DIR__ . "/../includes/admin_sidebar.php"); ?>

<div class="main">
    <div class="panel">
        <h1>Blood Stock</h1>

        <table>
            <thead>
                <tr>
                    <th>Stock ID</th>
                    <th>Blood Group</th>
                    <th>Units Available</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody id="stockTableBody">
                <tr>
                    <td colspan="4">Loading stock...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function loadStock() {
    $.ajax({
        url: '../api/stock.php',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            if (!res.success || !res.data) {
                $('#stockTableBody').html('<tr><td colspan="4">Failed to load stock.</td></tr>');
                return;
            }

            let html = '';

            if (res.data.length === 0) {
                html = '<tr><td colspan="4">No stock data available.</td></tr>';
            } else {
                res.data.forEach(row => {
                    html += `
                        <tr>
                            <td>${row.StockID}</td>
                            <td>${row.BloodGroup}</td>
                            <td>${row.UnitsAvailable}</td>
                            <td>${row.LastUpdated ?? ''}</td>
                        </tr>
                    `;
                });
            }

            $('#stockTableBody').html(html);
        },
        error: function() {
            $('#stockTableBody').html('<tr><td colspan="4">Error loading stock.</td></tr>');
        }
    });
}

$(document).ready(function() {
    loadStock();
});
</script>

</body>
</html>