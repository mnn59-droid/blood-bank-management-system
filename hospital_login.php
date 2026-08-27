<?php
include(__DIR__ . "/config.php");

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM Hospital WHERE Email=?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($hospital = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $hospital['Password'])) {
                $_SESSION['hospital_id'] = $hospital['HospitalID'];
                $_SESSION['hospital_name'] = $hospital['HospitalName'];
                header("Location: hospital/dashboard.php");
                exit();
            }
        }
        $error = "Invalid hospital email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hospital Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="center-page">
    <div class="form-card">

        <h2 style="margin-bottom: 16px;">Hospital Login</h2>

        <?php if (isset($error)) { ?>
            <p class="error" style="margin-bottom:12px;">
                <?php echo e($error); ?>
            </p>
        <?php } ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" name="login">Login</button>
        </form>

        <p style="text-align:center; margin-top:10px; font-size:14px;">
            New hospital? <a href="hospital_register.php">Register</a>
        </p>

    </div>
</div>

</body>
</html>