<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include(__DIR__ . "/config.php");

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM Donor WHERE Email=?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($donor = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $donor['Password'])) {
                $_SESSION['donor_id'] = $donor['DonorID'];
                $_SESSION['donor_name'] = isset($donor['FullName']) ? $donor['FullName'] : $donor['Name'];

                header("Location: donor/dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Donor Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="center-page">
    <div class="form-card">

        <h2 style="margin-bottom: 16px;">Donor Login</h2>

        <?php if (isset($error)) { ?>
            <p class="error" style="margin-bottom:12px;">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php } ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" name="login">Login</button>
        </form>

    </div>
</div>

</body>
</html>