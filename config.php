<?php

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$database = getenv('DB_NAME');
$port = getenv('DB_PORT') ?: 3306;

$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database,
    (int)$port
);

if (!$conn) {
    die("Database connection failed.");
}
?>
