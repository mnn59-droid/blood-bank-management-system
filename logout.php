<?php 
include(__DIR__ . "/config.php");
session_start(); // Start the session   
session_destroy(); // Destroy the session to log out the user   
header("Location: /bloodbank/"); // Redirect to the login page after logout
exit();
?>