<?php
require_once(__DIR__ . "/../config.php");

if (!isset($_SESSION['hospital_id'])) {
    header("Location: ../hospital_login.php");
    exit();
}

$hospitalID = (int)$_SESSION['hospital_id'];

/* Load current hospital */
$stmt = mysqli_prepare($conn, "SELECT * FROM Hospital WHERE HospitalID = ?");
mysqli_stmt_bind_param($stmt, "i", $hospitalID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$hospital = mysqli_fetch_assoc($result);

if (!$hospital) {
    session_unset();
    session_destroy();
    header("Location: ../hospital_login.php");
    exit();
}

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hospitalName  = trim($_POST['hospital_name'] ?? '');
    $contactPerson = trim($_POST['contact_person'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $address       = trim($_POST['address'] ?? '');
    $newPassword   = trim($_POST['password'] ?? '');

    if ($hospitalName === '' || $phone === '' || $email === '') {
        $error = "Hospital name, phone, and email are required.";
    } else {
        /* Check if email already belongs to another hospital */
        $checkStmt = mysqli_prepare($conn, "SELECT HospitalID FROM Hospital WHERE Email = ? AND HospitalID != ?");
        mysqli_stmt_bind_param($checkStmt, "si", $email, $hospitalID);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if (mysqli_num_rows($checkResult) > 0) {
            $error = "That email is already in use by another hospital.";
        } else {
            if ($newPassword !== '') {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $updateStmt = mysqli_prepare($conn, "
                    UPDATE Hospital
                    SET HospitalName = ?, ContactPerson = ?, Phone = ?, Email = ?, Address = ?, Password = ?
                    WHERE HospitalID = ?
                ");
                mysqli_stmt_bind_param(
                    $updateStmt,
                    "ssssssi",
                    $hospitalName,
                    $contactPerson,
                    $phone,
                    $email,
                    $address,
                    $hashedPassword,
                    $hospitalID
                );
            } else {
                $updateStmt = mysqli_prepare($conn, "
                    UPDATE Hospital
                    SET HospitalName = ?, ContactPerson = ?, Phone = ?, Email = ?, Address = ?
                    WHERE HospitalID = ?
                ");
                mysqli_stmt_bind_param(
                    $updateStmt,
                    "sssssi",
                    $hospitalName,
                    $contactPerson,
                    $phone,
                    $email,
                    $address,
                    $hospitalID
                );
            }

            if (mysqli_stmt_execute($updateStmt)) {
                $_SESSION['hospital_name'] = $hospitalName;
                $message = "Profile updated successfully.";

                /* Reload latest data */
                $reloadStmt = mysqli_prepare($conn, "SELECT * FROM Hospital WHERE HospitalID = ?");
                mysqli_stmt_bind_param($reloadStmt, "i", $hospitalID);
                mysqli_stmt_execute($reloadStmt);
                $reloadResult = mysqli_stmt_get_result($reloadStmt);
                $hospital = mysqli_fetch_assoc($reloadResult);
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hospital Profile</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/hospital_top.php"); ?>
<?php include("../includes/hospital_sidebar.php"); ?>

<div class="main">
    <div class="panel" style="max-width: 700px;">
        <h1>Hospital Profile</h1>
        <p style="color:#6b7280; margin-bottom:18px;">
            Update your hospital details, email, and password.
        </p>

        <?php if ($message !== "") { ?>
            <p style="color:green; font-weight:bold; margin-bottom:12px;"><?php echo e($message); ?></p>
        <?php } ?>

        <?php if ($error !== "") { ?>
            <p style="color:red; font-weight:bold; margin-bottom:12px;"><?php echo e($error); ?></p>
        <?php } ?>

        <form method="POST">
            <input
                type="text"
                name="hospital_name"
                placeholder="Hospital Name"
                value="<?php echo e($hospital['HospitalName']); ?>"
                required
            >

            <input
                type="text"
                name="contact_person"
                placeholder="Contact Person"
                value="<?php echo e($hospital['ContactPerson']); ?>"
            >

            <input
                type="text"
                name="phone"
                placeholder="Phone"
                value="<?php echo e($hospital['Phone']); ?>"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="Email"
                value="<?php echo e($hospital['Email']); ?>"
                required
            >

            <textarea
                name="address"
                placeholder="Address"
                rows="4"
            ><?php echo e($hospital['Address']); ?></textarea>

            <input
                type="password"
                name="password"
                placeholder="New Password (leave blank to keep current password)"
            >

            <button type="submit">Update Profile</button>
        </form>
    </div>
</div>
</body>
</html>