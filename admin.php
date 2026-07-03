<?php
session_start();
// Restrict to admin users only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'smartnotes';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

function escape_html(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

// Totals
$totalStudents = 0;
$totalNotes = 0;
$pendingNotes = 0;

$r = $conn->query("SELECT COUNT(*) AS c FROM student");
if ($r) { $row = $r->fetch_assoc(); $totalStudents = (int)$row['c']; $r->close(); }

$r = $conn->query("SELECT COUNT(*) AS c FROM notes");
if ($r) { $row = $r->fetch_assoc(); $totalNotes = (int)$row['c']; $r->close(); }

$r = $conn->query("SELECT COUNT(*) AS c FROM notes WHERE noteStatus='pending' OR noteType='pending' OR noteType='paid'");
if ($r) { $row = $r->fetch_assoc(); $pendingNotes = (int)$row['c']; $r->close(); }

// Recent students (best-effort)
$recentStudents = [];
$r = $conn->query("SELECT studentName, studentEmail, studentID FROM student ORDER BY studentID DESC LIMIT 4");
if ($r) {
    while ($row = $r->fetch_assoc()) { $recentStudents[] = $row; }
    $r->close();
}

// Pending notes sample
$pendingList = [];
$r = $conn->query("SELECT noteTitle, studentName FROM notes WHERE noteStatus='pending' OR noteType='pending' OR noteType='paid' LIMIT 5");
if ($r) {
    while ($row = $r->fetch_assoc()) { $pendingList[] = $row; }
    $r->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Dashboard - UiTM NoteLink</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style> .stat-card h3 { font-size: 1.6rem; } .badge.pending { background:#fff4e6; color:#d97706; padding:6px 10px; border-radius:999px;}</style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand"><img src="img/logo.PNG" alt="Logo" class="brand-logo-img"></div>
            <nav class="menu">
                <a href="admin.php" class="menu-item active"><span class="menu-icon"><i class="fas fa-th-large"></i></span><span>Dashboard</span></a>
                <a href="manage_students.php" class="menu-item"><span class="menu-icon"><i class="fas fa-users"></i></span><span>Manage Students</span></a>
                <a href="manage_notes.html" class="menu-item"><span class="menu-icon"><i class="far fa-file-alt"></i></span><span>Manage Notes</span></a>
                <a href="adminprofile.php" class="menu-item"><span class="menu-icon"><i class="fas fa-id-card"></i></span><span>Admin Profile</span></a>
                <div class="menu-divider"></div>
                <a href="contributors.php" class="menu-item"><span class="menu-icon"><i class="far fa-question-circle"></i></span><span>Contributors</span></a>
                <a href="login.php" class="menu-item sign-out"><span class="menu-icon"><i class="fas fa-sign-out-alt"></i></span><span>Sign Out</span></a>
            </nav>
        </aside>
        <main class="content">
            <header class="topbar">
                <div class="top-nav"><a href="contributors.php">Contributors</a><a href="admin.php" class="active">Dashboard</a></div>
                <div class="profile-area"><div class="profile-circle">P</div><i class="fas fa-chevron-down"></i></div>
            </header>

            <div class="welcome-header"><h2>Welcome back, Admin 👋</h2><p class="subtitle">Here’s what is happening on NoteShare today.</p></div>

            <section class="stats-grid">
                <article class="stat-card"><p class="stat-label">TOTAL STUDENTS</p><h3><?= escape_html((string)$totalStudents) ?></h3></article>
                <article class="stat-card"><p class="stat-label">TOTAL NOTES</p><h3><?= escape_html((string)$totalNotes) ?></h3></article>
                <article class="stat-card highlight-card"><p class="stat-label">PENDING APPROVAL</p><h3><?= escape_html((string)$pendingNotes) ?></h3></article>
                <article class="stat-card"><p class="stat-label">REVENUE</p><h3>RM 482</h3></article>
            </section>

            <section class="table-section">
                <div class="table-card recent-students">
                    <div class="table-card-header"><h3>Recent Students</h3><a href="manage_students.php" class="view-all">View All</a></div>
                    <table><thead><tr><th>NAME</th><th>EMAIL</th><th>STUDENT ID</th></tr></thead><tbody>
                        <?php foreach ($recentStudents as $s): ?>
                        <tr><td><?= escape_html($s['studentName']) ?></td><td><?= escape_html($s['studentEmail']) ?></td><td><?= escape_html($s['studentID']) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody></table>
                </div>

                <div class="table-card pending-notes">
                    <div class="table-card-header"><h3>Notes Pending Approval</h3><a href="manage_notes.html" class="view-all">View All</a></div>
                    <table><thead><tr><th>TITLE</th><th>BY</th><th>STATUS</th></tr></thead><tbody>
                        <?php foreach ($pendingList as $p): ?>
                        <tr><td><?= escape_html($p['noteTitle']) ?></td><td><?= escape_html($p['studentName'] ?? '') ?></td><td><span class="badge pending">Pending</span></td></tr>
                        <?php endforeach; ?>
                    </tbody></table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
