<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include(__DIR__ . "/config.php");

if (isset($_POST['register'])) {
    $fullname = trim($_POST['fullname']);
    $gender = trim($_POST['gender']);
    $dob = $_POST['dob'];
    $bloodgroup = trim($_POST['bloodgroup']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $rawPassword = trim($_POST['password']);
    $status = "Available";

    if (empty($fullname) || empty($gender) || empty($dob) || empty($bloodgroup) || empty($phone) || empty($email) || empty($rawPassword)) {
        $msg = "Please fill in all required fields.";
    } else {
        $password = password_hash($rawPassword, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO Donor (FullName, Gender, DOB, BloodGroup, Phone, Email, Address, Password, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssssssss", $fullname, $gender, $dob, $bloodgroup, $phone, $email, $address, $password, $status);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: donor_login.php?registered=1");
            exit();
        } else {
            $msg = "Registration failed. Email may already exist.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Donor Registration</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="center-page">
        <div class="form-card">
            <h2 style="margin-bottom: 16px;">Donor Registration</h2>

            <?php if (isset($msg)) { ?>
                <p class="error" style="margin-bottom:12px;">
                    <?php echo e($msg); ?>
                </p>
            <?php } ?>

            <form method="POST">
                <input type="text" name="fullname" placeholder="Full Name" required>

                <select name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>

                <input type="date" name="dob" required>

                <select name="bloodgroup" required>
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>

                <input type="text" name="phone" placeholder="Phone" required>
                <input type="email" name="email" placeholder="Email" required>
                <textarea name="address" placeholder="Address"></textarea>
                <input type="password" name="password" placeholder="Password" required>

                <button type="submit" name="register">Register</button>
            </form>

            <p style="text-align:center; margin-top:10px; font-size:14px;">
                Already have an account? <a href="donor_login.php">Login</a>
            </p>
        </div>
    </div>
</body>
</html>