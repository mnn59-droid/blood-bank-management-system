<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['admin_email'])) { exit(); }

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search === '') {
    $stmt = mysqli_prepare($conn, "
        SELECT Donor.*, COUNT(Donation.DonationID) AS total_donations
        FROM Donor
        LEFT JOIN Donation ON Donor.DonorID = Donation.DonorID
        GROUP BY Donor.DonorID
        ORDER BY Donor.DonorID DESC
    ");
} else {
    $stmt = mysqli_prepare($conn, "
        SELECT Donor.*, COUNT(Donation.DonationID) AS total_donations
        FROM Donor
        LEFT JOIN Donation ON Donor.DonorID = Donation.DonorID
        WHERE Donor.FullName LIKE CONCAT('%', ?, '%')
           OR Donor.BloodGroup LIKE CONCAT('%', ?, '%')
           OR Donor.Phone LIKE CONCAT('%', ?, '%')
           OR Donor.Email LIKE CONCAT('%', ?, '%')
        GROUP BY Donor.DonorID
        ORDER BY Donor.DonorID DESC
    ");
    mysqli_stmt_bind_param($stmt, "ssss", $search, $search, $search, $search);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['DonorID'] . "</td>";
    echo "<td>" . e($row['FullName']) . "</td>";
    echo "<td>" . e($row['Gender']) . "</td>";
    echo "<td>" . e($row['BloodGroup']) . "</td>";
    echo "<td>" . e($row['Phone']) . "</td>";
    echo "<td>" . e($row['Email']) . "</td>";
    echo "<td>" . e($row['Status']) . "</td>";

    echo "<td>";
    if ((int)$row['total_donations'] > 0) {
        echo "<span style='color:green; font-weight:bold;'>Donated</span>";
    } else {
        echo "<span style='color:red; font-weight:bold;'>Not Yet</span>";
    }
    echo "</td>";

    echo "<td>
            <a class='action-btn donate-btn' href='add_donation.php?donorID=" . $row['DonorID'] . "'>Donate</a>
            <a class='action-btn edit-btn' href='edit_donor.php?id=" . $row['DonorID'] . "'>Edit</a>
            <a class='action-btn delete-btn' href='delete_donor.php?id=" . $row['DonorID'] . "' onclick=\"return confirm('Delete this donor?')\">Delete</a>
          </td>";
    echo "</tr>";
}
?>