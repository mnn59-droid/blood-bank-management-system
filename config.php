<?php

// Load server-only credentials if present (not in Git)
if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// Priority: environment variable -> config.local.php -> local XAMPP default
$host     = getenv('DB_HOST')     ?: ($DB_HOST     ?? 'localhost');
$user     = getenv('DB_USER')     ?: ($DB_USER     ?? 'root');
$password = getenv('DB_PASSWORD') ?: ($DB_PASSWORD ?? '');
$database = getenv('DB_NAME')     ?: ($DB_NAME     ?? 'bloodbank');
$port     = getenv('DB_PORT')     ?: ($DB_PORT     ?? 3306);

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

mysqli_set_charset($conn, 'utf8mb4');
?>
