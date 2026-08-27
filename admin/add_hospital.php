<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['admin_email'])) { header("Location: ../login.php"); exit(); }

if (isset($_POST['save_hospital'])) {
    $name = trim($_POST['hospitalname']);
    $contact = trim($_POST['contactperson']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $rawPassword = trim($_POST['password']);

    if (empty($name) || empty($phone) || empty($email) || empty($rawPassword)) {
        $msg = "Please fill in all required fields.";
    } else {
        $password = password_hash($rawPassword, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO Hospital (HospitalName, ContactPerson, Phone, Email, Address, Password) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $name, $contact, $phone, $email, $address, $password);
        $msg = mysqli_stmt_execute($stmt) ? "Hospital added successfully." : "Failed to add hospital.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Hospital</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/admin_top.php"); ?>
<?php include("../includes/admin_sidebar.php"); ?>
<div class="main">
    <div class="panel">
        <h1>Add Hospital</h1>
        <?php if (isset($msg)) echo "<p>" . e($msg) . "</p>"; ?>
        <form method="POST">
            <input type="text" name="hospitalname" placeholder="Hospital Name">
            <input type="text" name="contactperson" placeholder="Contact Person">
            <input type="text" name="phone" placeholder="Phone">
            <input type="email" name="email" placeholder="Email">
            <textarea name="address" placeholder="Address"></textarea>
            <input type="password" name="password" placeholder="Hospital Login Password">
            <button type="submit" name="save_hospital">Save Hospital</button>
        </form>
    </div>
</div>
</body>
</html>