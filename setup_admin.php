<?php
include(__DIR__ . "/config.php");

$email = "admin@bloodbank.com";
$password = "admin123";
$fullName = "Admin User";

$check = mysqli_query($conn, "SELECT * FROM Admin WHERE Email='$email'");

if (mysqli_num_rows($check) == 0) {

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $insert = mysqli_query(
        $conn,
        "INSERT INTO Admin (FullName, Email, Password) 
         VALUES ('$fullName', '$email', '$hashed_password')"
    );

    if ($insert) {
        echo "Default admin user created successfully.<br>";
        echo "Email: admin@bloodbank.com<br>";
        echo "Password: admin123";
    } else {
        echo "Error creating default admin user: " . mysqli_error($conn);
    }

} else {
    echo "Default admin user already exists.";
}
?>