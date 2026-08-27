<?php
include(__DIR__ . "/../config.php");
include(__DIR__ . "/response.php");

$result = mysqli_query($conn, "
    SELECT MONTH(DonationDate) AS month, COUNT(*) AS total
    FROM Donation
    GROUP BY MONTH(DonationDate)
    ORDER BY month
");

$trend = [];
while ($row = mysqli_fetch_assoc($result)) {
    $trend[] = $row;
}

jsonResponse(true, "Donation trend fetched successfully", $trend);
?>