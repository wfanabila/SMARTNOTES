<?php
require_once __DIR__ . '/admin_bootstrap.php';
$profileRole = trim((string) ($admin['adminRole'] ?? '')) ?: 'Administrator';
$profilePhone = trim((string) ($admin['adminPhone'] ?? '')) ?: 'Not set';
$profileDepartment = trim((string) ($admin['adminDepartment'] ?? '')) ?: 'Not set';
$memberSince = !empty($admin['memberSince']) ? date('F Y', strtotime($admin['memberSince'])) : 'Not set';
$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Profile | UiTM NoteLink</title>
    <link rel="stylesheet" href="css/adminprofile.css">
    <link rel="stylesheet" href="css/admin_profile_override.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="admin-profile-layout">
    <aside class="admin-profile-sidebar">
        <a href="admin_dashboard.php" class="admin-profile-brand"><img src="img/logo.PNG" alt="UiTM NoteLink"></a>
        <nav class="admin-profile-menu">
            <a href="admin_dashboard.php"><i class="far fa-circle"></i><span>Dashboard</span></a>
            <a href="manage_students.php"><i class="far fa-user"></i><span>Manage Students</span></a>
            <a href="manage_notes.php"><i class="far fa-square"></i><span>Manage Notes</span></a>
            <a class="active" href="adminprofile.php"><i class="far fa-user"></i><span>Admin's Profile</span></a>
        </nav>
        <div class="admin-profile-account">
            <p>Account</p>
            <div class="admin-profile-account__identity"><span><?= admin_escape($adminInitial) ?></span><div><b><?= admin_escape($adminName) ?></b><small><?= admin_escape($adminEmail) ?></small></div></div>
            <a class="active" href="admin_account_setting.php"><i class="fas fa-circle-info"></i> Account Setting</a>
            <a href="logout.php"><i class="fas fa-arrow-right-from-bracket"></i> Log Out</a>
        </div>
        <a class="admin-profile-settings" href="admin_account_setting.php"><i class="fas fa-sun"></i> Settings</a>
    </aside>
    <main class="admin-profile-main">
        <header class="admin-profile-topbar"><nav><a href="admin_contributors.php">Contributors</a><a class="active" href="admin_dashboard.php">Dashboard</a></nav><a href="adminprofile.php" class="admin-profile-top-avatar"><?= admin_escape($adminInitial) ?></a></header>
        <section class="admin-profile-content">
            <h1>Admin Profile</h1><p class="admin-profile-subtitle">Your account information.</p>
            <article class="admin-profile-card">
                <div class="admin-profile-summary"><div class="admin-profile-initial"><?php if (!empty($admin['profilePicture'])): ?><img src="<?= admin_escape($admin['profilePicture']) ?>" alt="Profile picture"><?php else: ?><?= admin_escape($adminInitial) ?><?php endif; ?></div><div><h2><?= admin_escape($adminName) ?></h2><p><?= admin_escape($adminEmail) ?></p><span><?= admin_escape($profileRole) ?></span></div></div>
                <div class="admin-profile-line"></div>
                <div class="admin-profile-details">
                    <div><label>Full Name</label><strong><?= admin_escape($adminName) ?></strong></div>
                    <div><label>Email</label><strong><?= admin_escape($adminEmail) ?></strong></div>
                    <div><label>Phone</label><strong><?= admin_escape($profilePhone) ?></strong></div>
                    <div><label>Role</label><strong><?= admin_escape($profileRole) ?></strong></div>
                    <div><label>Department</label><strong><?= admin_escape($profileDepartment) ?></strong></div>
                    <div><label>Member Since</label><strong><?= admin_escape($memberSince) ?></strong></div>
                </div>
            </article>
        </section>
    </main>
</div>
</body></html>
