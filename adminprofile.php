<?php
$adminName = 'Putri Amira binti Abdullah';
$adminEmail = 'putri@admin.noteshare.my';
$adminPhone = '+60 12-345 6789';
$adminRole = 'Super Admin';
$adminDept = 'Computer Science';
$adminSince = 'January 2025';
$adminInitial = strtoupper(substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTMNoteLink - Admin Profile</title>
    <link rel="stylesheet" href="css/adminprofile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <img src="img/logo.PNG" alt="UiTMNoteLink Logo" class="brand-logo-img">
            </div>
            <nav class="menu">
                <a href="admin.html" class="menu-item">
                    <span class="menu-icon"><i class="fas fa-th-large"></i></span>
                    <span>Dashboard</span>
                </a>
                <a href="manage_students.html" class="menu-item">
                    <span class="menu-icon"><i class="fas fa-users"></i></span>
                    <span>Manage Students</span>
                </a>
                <a href="manage_notes.html" class="menu-item">
                    <span class="menu-icon"><i class="far fa-file-alt"></i></span>
                    <span>Manage Notes</span>
                </a>
                <a href="adminprofile.php" class="menu-item active">
                    <span class="menu-icon"><i class="fas fa-id-card"></i></span>
                    <span>Admin’s Profile</span>
                </a>
                <div class="menu-divider"></div>
                <a href="help_center.html" class="menu-item">
                    <span class="menu-icon"><i class="far fa-question-circle"></i></span>
                    <span>Help Center</span>
                </a>
                <a href="login.php" class="menu-item sign-out">
                    <span class="menu-icon"><i class="fas fa-sign-out-alt"></i></span>
                    <span>Sign Out</span>
                </a>
            </nav>
        </aside>

        <main class="content">
            <header class="topbar">
                <div class="top-nav">
                    <a href="help_center.html">Contributors</a>
                    <a href="admin.html" class="active">Dashboard</a>
                </div>
                <div class="profile-area">
                    <div class="profile-circle"><?= htmlspecialchars($adminInitial) ?></div>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </header>

            <div class="page-head">
                <div>
                    <h1>Admin Profile</h1>
                    <p class="page-subtitle">Your account information.</p>
                </div>
            </div>

            <section class="profile-panel">
                <div class="profile-card">
                    <div class="profile-card__avatar">
                        <div class="profile-initials">P</div>
                        <div>
                            <h2><?= htmlspecialchars($adminName) ?></h2>
                            <p class="email"><?= htmlspecialchars($adminEmail) ?></p>
                        </div>
                    </div>
                    <div class="profile-badge"><?= htmlspecialchars($adminRole) ?></div>
                </div>

                <div class="profile-info-grid">
                    <div class="info-item">
                        <p class="info-label">Full Name</p>
                        <p class="info-value"><?= htmlspecialchars($adminName) ?></p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Email</p>
                        <p class="info-value"><?= htmlspecialchars($adminEmail) ?></p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Phone</p>
                        <p class="info-value"><?= htmlspecialchars($adminPhone) ?></p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Role</p>
                        <p class="info-value"><?= htmlspecialchars($adminRole) ?></p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Department</p>
                        <p class="info-value"><?= htmlspecialchars($adminDept) ?></p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Member Since</p>
                        <p class="info-value"><?= htmlspecialchars($adminSince) ?></p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
