<?php
require_once(__DIR__ . "/config.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank System</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/index.css">
</head>
<body>

        <div class="bg-layer"></div>
        <div class="drop"></div>
        <div class="drop"></div>
        <div class="drop"></div>
        <div class="drop"></div>
        
            <div class="login-card">
                <div class="logo-wrap">
                    <div class="logo-icon">🩸</div>
                    <div>
                        <div class="logo-title">Blood Bank</div>
                        <div class="logo-sub">Management System</div>
                    </div>
                </div>
            
            <div class="status-bar">
                <div class="status-dot"></div>
                <span class="status-text">System online</span>
            </div>

            <div class="divider"></div>

            <p class="card-heading">Select Portal</p>

            <a href="login.php" class="nav-btn primary">
                <span class="btn-icon">👤</span>
                Admin Login
                <span class="btn-arrow">›</span>
            </a>

            <a href="donor_login.php" class="nav-btn">
                <span class="btn-icon">🫀</span>
                Donor Login
                <span class="btn-arrow">›</span>
            </a>

            <a href="donor_register.php" class="nav-btn">
                <span class="btn-icon">＋</span>
                Donor Registration
                <span class="btn-arrow">›</span>
            </a>

            <a href="hospital_login.php" class="nav-btn">
                <span class="btn-icon">🏥</span>
                Hospital Login
                <span class="btn-arrow">›</span>
            </a>

            <a href="setup_admin.php" class="setup-link">Create default admin</a>
        </div>

</body>
</html>