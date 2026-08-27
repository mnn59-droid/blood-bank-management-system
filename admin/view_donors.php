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
    <title>View Donors</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include(__DIR__ . "/../includes/admin_top.php"); ?>
<?php include(__DIR__ . "/../includes/admin_sidebar.php"); ?>

<div class="main">
    <h1>Donor Management</h1>

    <div class="panel">
        <div class="filter-bar">
            <input type="text" id="liveSearch" placeholder="Live search donor by name, blood group, phone, or email">
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Blood Group</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Donation Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="donorResults">
                <tr>
                    <td colspan="9">Loading donors...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {

    function renderDonors(donors) {
        let html = "";

        if (!donors || donors.length === 0) {
            html = `<tr><td colspan="9">No donors found.</td></tr>`;
            $('#donorResults').html(html);
            return;
        }

        donors.forEach(donor => {
            const totalDonations = parseInt(donor.total_donations || 0, 10);
            const donationStatus = totalDonations > 0
                ? `<span style="color:green; font-weight:bold;">Donated</span>`
                : `<span style="color:red; font-weight:bold;">Not Yet</span>`;

            html += `
                <tr>
                    <td>${donor.DonorID}</td>
                    <td>${donor.FullName ?? ''}</td>
                    <td>${donor.Gender ?? ''}</td>
                    <td>${donor.BloodGroup ?? ''}</td>
                    <td>${donor.Phone ?? ''}</td>
                    <td>${donor.Email ?? ''}</td>
                    <td>${donor.Status ?? ''}</td>
                    <td>${donationStatus}</td>
                    <td>
                        <a class="action-btn donate-btn" href="add_donation.php?donorID=${donor.DonorID}">Donate</a>
                        <a class="action-btn edit-btn" href="edit_donor.php?id=${donor.DonorID}">Edit</a>
                        <button type="button" class="delete-btn action-btn" data-id="${donor.DonorID}">Delete</button>
                    </td>
                </tr>
            `;
        });

        $('#donorResults').html(html);
    }

    function loadDonors(query = "") {
        $('#donorResults').html('<tr><td colspan="9">Loading donors...</td></tr>');

        $.ajax({
            url: '../api/donors.php',
            method: 'GET',
            dataType: 'json',
            data: { q: query },
            success: function (res) {
                if (!res.success || !res.data) {
                    $('#donorResults').html('<tr><td colspan="9">Failed to load donors.</td></tr>');
                    return;
                }

                renderDonors(res.data);
            },
            error: function () {
                $('#donorResults').html('<tr><td colspan="9">Error loading donor data.</td></tr>');
            }
        });
    }

    $(document).on('click', '.delete-btn', function () {
        if (!confirm('Delete this donor?')) return;

        const button = $(this);
        const donorID = button.data('id');

        $.ajax({
            url: 'delete_donor.php',
            method: 'POST',
            data: { donor_id: donorID },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    button.closest('tr').remove();
                } else {
                    alert(res.message || 'Failed to delete donor.');
                }
            },
            error: function () {
                alert('Error deleting donor.');
            }
        });
    });

    loadDonors();

    $('#liveSearch').on('keyup', function () {
        loadDonors($(this).val());
    });
});
</script>
</body>
</html>