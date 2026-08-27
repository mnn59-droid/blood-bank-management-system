<?php
include(__DIR__ . "/../config.php");
include(__DIR__ . "/response.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT * FROM BloodStock ORDER BY BloodGroup ASC";
    $result = mysqli_query($conn, $sql);

    $stock = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stock[] = $row;
    }

    jsonResponse(true, "Stock fetched successfully", $stock);
}

jsonResponse(false, "Method not allowed", null, 405);
?>