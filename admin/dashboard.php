<?php
require_once(__DIR__ . "/../config.php");
if (!isset($_SESSION['admin_email'])) { header("Location: ../login.php"); exit(); }

$donor_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Donor"))['total'];
$hospital_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Hospital"))['total'];
$request_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM BloodRequest"))['total'];
$approved_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM BloodRequest WHERE Status='Approved'"))['total'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM BloodRequest WHERE Status='Pending'"))['total'];
$stock_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(UnitsAvailable), 0) AS total FROM BloodStock"))['total'];
$donation_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Donation"))['total'];

$lowStockResult = mysqli_query($conn, "SELECT * FROM BloodStock WHERE UnitsAvailable <= 5 ORDER BY UnitsAvailable ASC");
$lowStockCount = mysqli_num_rows($lowStockResult);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include(__DIR__ . "/../includes/admin_top.php"); ?>
<?php include(__DIR__ . "/../includes/admin_sidebar.php"); ?>

<div class="main">
    <div class="panel" style="margin-bottom: 20px;">
        <h1 style="margin-bottom: 8px;">Admin Dashboard</h1>
        <p style="color:#6b7280; margin:0;">
            Monitor donors, hospitals, blood requests, stock levels, and donation activity in one place.
        </p>
    </div>

    <div class="cards">
        <a href="view_donors.php" class="card-link">
            <div class="card">
                <h3>Total Donors</h3>
                <p class="counter" data-target="<?php echo (int)$donor_count; ?>">0</p>
            </div>
        </a>

        <a href="view_hospitals.php" class="card-link">
            <div class="card">
                <h3>Total Hospitals</h3>
                <p class="counter" data-target="<?php echo (int)$hospital_count; ?>">0</p>
            </div>
        </a>

        <a href="manage_requests.php" class="card-link">
            <div class="card">
                <h3>Total Requests</h3>
                <p class="counter" data-target="<?php echo (int)$request_count; ?>">0</p>
            </div>
        </a>

        <a href="manage_requests.php?status=Approved" class="card-link">
            <div class="card">
                <h3>Approved Requests</h3>
                <p class="counter" data-target="<?php echo (int)$approved_count; ?>">0</p>
            </div>
        </a>

        <a href="manage_requests.php?status=Pending" class="card-link">
            <div class="card">
                <h3>Pending Requests</h3>
                <p class="counter" data-target="<?php echo (int)$pending_count; ?>">0</p>
            </div>
        </a>

        <a href="view_stock.php" class="card-link">
            <div class="card">
                <h3>Total Units</h3>
                <p class="counter" data-target="<?php echo (int)$stock_count; ?>">0</p>
            </div>
        </a>

        <a href="view_donations.php" class="card-link">
            <div class="card">
                <h3>Total Donations</h3>
                <p class="counter" data-target="<?php echo (int)$donation_count; ?>">0</p>
            </div>
        </a>

        <a href="view_stock.php?low=1" class="card-link">
            <div class="card">
                <h3>Low Stock Alerts</h3>
                <p class="counter" data-target="<?php echo (int)$lowStockCount; ?>">0</p>
            </div>
        </a>
    </div>

    <div class="panel">
        <h2>Blood Stock Levels</h2>
        <div class="chart-container">
            <canvas id="bloodChart"></canvas>
        </div>
    </div>

    <div class="panel">
        <h2>Blood Group Distribution</h2>
        <div class="chart-container">
            <canvas id="bloodPie"></canvas>
        </div>
    </div>

    <div class="panel">
        <h2>Monthly Donations</h2>
        <div class="chart-container">
            <canvas id="donationLine"></canvas>
        </div>
    </div>

    <div class="panel">
        <h2>Low Blood Stock Alerts</h2>
        <?php if ($lowStockCount > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Blood Group</th>
                        <th>Units Available</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($lowStockResult)) { ?>
                        <tr>
                            <td><?php echo e($row['BloodGroup']); ?></td>
                            <td><?php echo (int)$row['UnitsAvailable']; ?></td>
                            <td class="alert-low">Low Stock</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>All blood groups are sufficiently stocked.</p>
        <?php } ?>
    </div>

    <div class="panel">
        <h2>Recent Blood Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hospital</th>
                    <th>Blood Group</th>
                    <th>Units</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="recentRequestsBody">
                <tr>
                    <td colspan="6">Loading requests...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
let bloodChart;
let bloodPieChart;
let donationLineChart;

function loadStockCharts() {
    $.ajax({
        url: '../api/stock.php',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            if (!res.success || !res.data) return;

            const labels = res.data.map(item => item.BloodGroup);
            const units = res.data.map(item => parseInt(item.UnitsAvailable));

            const barCtx = document.getElementById('bloodChart').getContext('2d');
            const pieCtx = document.getElementById('bloodPie').getContext('2d');

            if (bloodChart) bloodChart.destroy();
            if (bloodPieChart) bloodPieChart.destroy();

            bloodChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Units Available',
                        data: units,
                        backgroundColor: [
                            '#d9534f', '#0275d8', '#5cb85c', '#f0ad4e',
                            '#5bc0de', '#292b2c', '#ff6384', '#36a2eb'
                        ],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            bloodPieChart = new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: units,
                        backgroundColor: [
                            '#d9534f', '#0275d8', '#5cb85c', '#f0ad4e',
                            '#5bc0de', '#292b2c', '#ff6384', '#36a2eb'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    });
}

function loadDonationTrendChart() {
    $.ajax({
        url: '../api/donation_trend.php',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            if (!res.success || !res.data) return;

            const labels = res.data.map(item => item.month);
            const totals = res.data.map(item => parseInt(item.total));

            const lineCtx = document.getElementById('donationLine').getContext('2d');

            if (donationLineChart) donationLineChart.destroy();

            donationLineChart = new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Donations',
                        data: totals,
                        borderColor: '#d9534f',
                        backgroundColor: 'rgba(217, 83, 79, 0.12)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    });
}

function loadRecentRequests() {
    $.ajax({
        url: '../api/requests.php',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            if (!res.success || !res.data) {
                $('#recentRequestsBody').html('<tr><td colspan="6">Failed to load requests.</td></tr>');
                return;
            }

            let html = "";
            const requests = res.data.slice(0, 5);

            if (requests.length === 0) {
                html = `<tr><td colspan="6">No requests found.</td></tr>`;
            } else {
                requests.forEach(r => {
                    let statusColor = "#dc2626";
                    if (r.Status === "Approved") statusColor = "#16a34a";
                    else if (r.Status === "Pending") statusColor = "#d97706";

                    html += `
                        <tr>
                            <td>${r.RequestID}</td>
                            <td>${r.HospitalName ? r.HospitalName : 'N/A'}</td>
                            <td>${r.BloodGroup}</td>
                            <td>${r.UnitsRequested}</td>
                            <td>${r.RequestDate}</td>
                            <td style="color:${statusColor}; font-weight:bold;">${r.Status}</td>
                        </tr>
                    `;
                });
            }

            $('#recentRequestsBody').html(html);
        },
        error: function() {
            $('#recentRequestsBody').html('<tr><td colspan="6">Error loading requests.</td></tr>');
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const counters = document.querySelectorAll(".counter");

    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute("data-target"), 10) || 0;
        let current = 0;
        const increment = Math.max(1, Math.ceil(target / 40));

        const updateCounter = () => {
            current += increment;

            if (current >= target) {
                counter.textContent = target;
            } else {
                counter.textContent = current;
                requestAnimationFrame(updateCounter);
            }
        };

        updateCounter();
    });

    loadStockCharts();
    loadDonationTrendChart();
    loadRecentRequests();
});
</script>
</body>
</html>