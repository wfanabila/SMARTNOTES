<?php
require_once __DIR__ . '/admin_bootstrap.php';
$activeAdminPage = 'dashboard';

function scalar_count(mysqli $conn, string $sql): int {
    $result = $conn->query($sql);
    if (!$result) return 0;
    $row = $result->fetch_row();
    $result->close();
    return (int) ($row[0] ?? 0);
}
function table_columns(mysqli $conn, string $table): array {
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM `$table`");
    if ($result) { while ($row = $result->fetch_assoc()) $columns[] = $row['Field']; $result->close(); }
    return $columns;
}

$noteColumns = table_columns($conn, 'notes');
$statusColumn = in_array('noteStatus', $noteColumns, true) ? 'noteStatus' : null;
$totalStudents = scalar_count($conn, 'SELECT COUNT(*) FROM student');
$totalNotes = scalar_count($conn, 'SELECT COUNT(*) FROM notes');
$pendingNotes = $statusColumn ? scalar_count($conn, "SELECT COUNT(*) FROM notes WHERE `$statusColumn` = 'pending'") : 0;
$recentStudents = [];
$result = $conn->query('SELECT studentName, studentEmail, studentID FROM student ORDER BY studentID DESC LIMIT 5');
if ($result) { while ($row = $result->fetch_assoc()) $recentStudents[] = $row; $result->close(); }
$recentNotes = [];
$statusSelect = $statusColumn ? "n.`$statusColumn` AS noteStatus" : "'Published' AS noteStatus";
$result = $conn->query("SELECT n.title, s.studentName, $statusSelect FROM notes n LEFT JOIN student s ON s.studentID = n.studentID ORDER BY n.noteID DESC LIMIT 5");
if ($result) { while ($row = $result->fetch_assoc()) $recentNotes[] = $row; $result->close(); }
$conn->close();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Dashboard | UiTM NoteLink</title><link rel="stylesheet" href="css/admin.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></head>
<body><div class="layout"><?php include __DIR__ . '/admin_nav.php'; ?><main class="content">
<header class="topbar"><div class="top-nav"><a href="admin.php" class="active">Dashboard</a></div><a class="profile-area" href="adminprofile.php" aria-label="Open profile"><div class="profile-circle"><?= admin_escape($adminInitial) ?></div><span><?= admin_escape($adminName) ?></span></a></header>
<div class="welcome-header"><h2>Welcome back, <?= admin_escape($adminName) ?> &#128075;</h2><p class="subtitle">Here is the latest activity in UiTM NoteLink.</p></div>
<section class="stats-grid"><article class="stat-card"><p class="stat-label">Total Students</p><h3><?= $totalStudents ?></h3></article><article class="stat-card"><p class="stat-label">Total Notes</p><h3><?= $totalNotes ?></h3></article><article class="stat-card highlight-card"><p class="stat-label">Pending Approval</p><h3><?= $pendingNotes ?></h3></article><article class="stat-card"><p class="stat-label">Your Admin Account</p><h3><?= admin_escape($adminInitial) ?></h3><p class="subtitle">Active</p></article></section>
<section class="table-section"><div class="table-card recent-students"><div class="table-card-header"><h3>Recent Students</h3><a href="manage_students.php" class="view-all">View all</a></div><div class="table-scroll"><table><thead><tr><th>Name</th><th>Email</th><th>Student ID</th></tr></thead><tbody><?php if (!$recentStudents): ?><tr><td colspan="3">No students yet.</td></tr><?php endif; ?><?php foreach ($recentStudents as $student): ?><tr><td><?= admin_escape($student['studentName']) ?></td><td><?= admin_escape($student['studentEmail']) ?></td><td><?= admin_escape($student['studentID']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<div class="table-card pending-notes"><div class="table-card-header"><h3>Latest Notes</h3><a href="manage_notes.php" class="view-all">Manage notes</a></div><div class="table-scroll"><table><thead><tr><th>Title</th><th>By</th><th>Status</th></tr></thead><tbody><?php if (!$recentNotes): ?><tr><td colspan="3">No notes yet.</td></tr><?php endif; ?><?php foreach ($recentNotes as $note): ?><tr><td><?= admin_escape($note['title']) ?></td><td><?= admin_escape($note['studentName'] ?? 'Unknown') ?></td><td><span class="badge pending"><?= admin_escape(ucfirst((string)$note['noteStatus'])) ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></section>
</main></div></body></html>
