<?php
if (!isset($activeAdminPage)) { $activeAdminPage = ''; }
?>
<link rel="stylesheet" href="css/admin_sidebar.css">
<aside class="sidebar">
    <div class="brand"><img src="img/logo.PNG" alt="UiTM NoteLink" class="brand-logo-img"></div>
    <nav class="menu">
        <a href="admin_dashboard.php" class="menu-item <?= $activeAdminPage === 'dashboard' ? 'active' : '' ?>"><span class="menu-icon"><i class="fas fa-th-large"></i></span><span>Dashboard</span></a>
        <a href="manage_students.php" class="menu-item <?= $activeAdminPage === 'students' ? 'active' : '' ?>"><span class="menu-icon"><i class="fas fa-users"></i></span><span>Manage Students</span></a>
        <a href="admin_notes.php" class="menu-item <?= $activeAdminPage === 'browse_notes' ? 'active' : '' ?>"><span class="menu-icon"><i class="far fa-file"></i></span><span>Notes</span></a>
        <a href="manage_notes.php" class="menu-item <?= $activeAdminPage === 'notes' ? 'active' : '' ?>"><span class="menu-icon"><i class="far fa-file-alt"></i></span><span>Manage Notes</span></a>
        <a href="adminprofile.php" class="menu-item <?= $activeAdminPage === 'profile' ? 'active' : '' ?>"><span class="menu-icon"><i class="fas fa-id-card"></i></span><span>Admin Profile</span></a>
        <div class="menu-divider"></div>
        <a href="admin_contributors.php" class="menu-item <?= $activeAdminPage === 'contributors' ? 'active' : '' ?>"><span class="menu-icon"><i class="fas fa-trophy"></i></span><span>Contributors</span></a>
        <a href="help_center.php" class="menu-item <?= $activeAdminPage === 'help' ? 'active' : '' ?>"><span class="menu-icon"><i class="far fa-question-circle"></i></span><span>Help Center</span></a>
        <a href="admin_account_setting.php" class="menu-item"><span class="menu-icon"><i class="fas fa-cog"></i></span><span>Settings</span></a>
        <a href="logout.php" class="menu-item sign-out"><span class="menu-icon"><i class="fas fa-sign-out-alt"></i></span><span>Sign Out</span></a>
    </nav>
</aside>
