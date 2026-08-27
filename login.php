<?php
include(__DIR__ . "/config.php");

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM Admin WHERE Email=?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($admin = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $admin['Password'])) {
                $_SESSION['admin_email'] = $admin['Email'];
                $_SESSION['admin_name'] = $admin['FullName'];
                header("Location: admin/dashboard.php");
                exit();
            }
        }
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="center-page">
        <div class="form-card">
            <h2 style="margin-bottom: 16px;">Admin Login</h2>

            <?php if (isset($error)) echo "<p class='error' style='margin-bottom:12px;'>" . e($error) . "</p>"; ?>

            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>