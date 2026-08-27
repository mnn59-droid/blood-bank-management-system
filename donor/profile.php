<?php
include(__DIR__ . "/../config.php");
if (!isset($_SESSION['donor_id'])) { header("Location: ../donor_login.php"); exit(); }

$donorID = $_SESSION['donor_id'];
$donor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Donor WHERE DonorID = $donorID"));

if (isset($_POST['update'])) {
    $fullname = trim($_POST['fullname']);
    $gender = trim($_POST['gender']);
    $dob = $_POST['dob'];
    $bloodgroup = trim($_POST['bloodgroup']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $status = trim($_POST['status']);
    $newPassword = trim($_POST['password']);

    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE Donor SET FullName=?, Gender=?, DOB=?, BloodGroup=?, Phone=?, Email=?, Address=?, Status=?, Password=? WHERE DonorID=?");
        mysqli_stmt_bind_param($stmt, "sssssssssi", $fullname, $gender, $dob, $bloodgroup, $phone, $email, $address, $status, $hashedPassword, $donorID);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE Donor SET FullName=?, Gender=?, DOB=?, BloodGroup=?, Phone=?, Email=?, Address=?, Status=? WHERE DonorID=?");
        mysqli_stmt_bind_param($stmt, "ssssssssi", $fullname, $gender, $dob, $bloodgroup, $phone, $email, $address, $status, $donorID);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['donor_name'] = $fullname;
        header("Location: profile.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Profile</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/donor_top.php"); ?>
<?php include("../includes/donor_sidebar.php"); ?>
<div class="main">
    <div class="panel">
        <h1>Update Profile</h1>
        <form method="POST">
            <input type="text" name="fullname" value="<?php echo e($donor['FullName']); ?>">
            <select name="gender">
                <option value="Male" <?php if ($donor['Gender']=="Male") echo "selected"; ?>>Male</option>
                <option value="Female" <?php if ($donor['Gender']=="Female") echo "selected"; ?>>Female</option>
            </select>
            <input type="date" name="dob" value="<?php echo e($donor['DOB']); ?>">
            <select name="bloodgroup">
                <?php foreach (["A+","A-","B+","B-","AB+","AB-","O+","O-"] as $g) { ?>
                    <option value="<?php echo $g; ?>" <?php if ($donor['BloodGroup']==$g) echo "selected"; ?>><?php echo $g; ?></option>
                <?php } ?>
            </select>
            <input type="text" name="phone" value="<?php echo e($donor['Phone']); ?>">
            <input type="email" name="email" value="<?php echo e($donor['Email']); ?>">
            <textarea name="address"><?php echo e($donor['Address']); ?></textarea>
            <input type="password" name="password" placeholder="New password only if changing">
            <select name="status">
                <option value="Available" <?php if ($donor['Status']=="Available") echo "selected"; ?>>Available</option>
                <option value="Not Available" <?php if ($donor['Status']=="Not Available") echo "selected"; ?>>Not Available</option>
            </select>
            <button type="submit" name="update">Update Profile</button>
        </form>
    </div>
</div>
</body>
</html>