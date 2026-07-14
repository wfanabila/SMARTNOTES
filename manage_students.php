<?php
require_once __DIR__ . '/admin_bootstrap.php';
$activeAdminPage = 'students';
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "smartnotes";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Detect optional student fields if they exist.
$studentColumns = [];
$columnResult = $conn->query("SHOW COLUMNS FROM student");
if ($columnResult) {
    while ($col = $columnResult->fetch_assoc()) {
        $studentColumns[] = $col['Field'];
    }
}

$hasStatus = in_array('status', $studentColumns);
$hasJoined = in_array('joined', $studentColumns) || in_array('joinedAt', $studentColumns) || in_array('joined_date', $studentColumns);
$hasCreatedAt = in_array('created_at', $studentColumns) || in_array('createdAt', $studentColumns) || in_array('registration_date', $studentColumns);

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $targetStudentID = isset($_POST['studentID']) ? trim($_POST['studentID']) : '';
    $newStatus = isset($_POST['newStatus']) ? trim($_POST['newStatus']) : '';

    if ($hasStatus && $targetStudentID !== '' && in_array($newStatus, ['Active', 'Suspended', 'Pending'], true)) {
        $stmt = $conn->prepare("UPDATE student SET status = ? WHERE studentID = ?");
        if ($stmt) {
            $stmt->bind_param('ss', $newStatus, $targetStudentID);
            $stmt->execute();
            if ($stmt->affected_rows >= 0) {
                $feedback = "Status updated successfully.";
            } else {
                $feedback = "Unable to update student status.";
            }
            $stmt->close();
        }
    } else {
        $feedback = "Status update is not available for this student record.";
    }
}

$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? 'all');
$statusFilterValues = ['all', 'Active', 'Pending', 'Suspended'];
if (!in_array($statusFilter, $statusFilterValues, true)) {
    $statusFilter = 'all';
}

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(s.studentName LIKE ? OR s.studentEmail LIKE ? OR s.studentID LIKE ? OR s.programme LIKE ? OR s.semester LIKE ? )";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sssss';
}

if ($statusFilter !== 'all' && $hasStatus) {
    $where[] = "s.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

$statusSelect = $hasStatus ? 's.status' : '"Active"';
if ($hasJoined) {
    $joinedField = in_array('joined', $studentColumns) ? 'joined' : (in_array('joinedAt', $studentColumns) ? 'joinedAt' : 'joined_date');
    $joinedExpr = "DATE_FORMAT(s.$joinedField, '%d %b %Y')";
} elseif ($hasCreatedAt) {
    $createdField = in_array('created_at', $studentColumns) ? 'created_at' : (in_array('createdAt', $studentColumns) ? 'createdAt' : 'registration_date');
    $joinedExpr = "DATE_FORMAT(s.$createdField, '%d %b %Y')";
} else {
    $joinedExpr = 'NULL';
}

$sql = "SELECT s.studentID, s.studentName, s.studentEmail, $statusSelect AS status,
        COALESCE($joinedExpr, '') AS joined,
        (SELECT COUNT(*) FROM notes n WHERE n.studentID = s.studentID) AS notesUploaded
        FROM student s";

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY s.studentName ASC';

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Query preparation failed: ' . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();
$conn->close();

function badgeClass(string $status): string {
    $normalized = strtolower(trim($status));
    if ($normalized === 'active') return 'active';
    if ($normalized === 'pending') return 'pending';
    if ($normalized === 'suspended') return 'suspended';
    return 'pending';
}

function escape(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTM NoteLink Admin - Manage Students</title>
    <link rel="stylesheet" href="css/manage_students.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="layout">
        <?php include __DIR__ . '/admin_nav.php'; ?>
        <main class="content">
            <header class="topbar">
                <div class="top-nav">
                    <a href="admin_contributors.php">Contributors</a>
                    <a href="admin.php">Dashboard</a>
                </div>
                <a class="profile-area" href="adminprofile.php" aria-label="Open profile">
                    <div class="profile-circle"><?= admin_escape($adminInitial) ?></div>
                    <span><?= admin_escape($adminName) ?></span>
                </a>
            </header>

            <div class="section-header">
                <div class="section-title">
                    <h1>Manage Students</h1>
                    <p>View and manage all registered students.</p>
                </div>
                <div class="action-group">
                    <button class="btn btn-primary" onclick="window.location.href='register.php'">Add Student</button>
                </div>
            </div>

            <div class="controls">
                <form class="search-box" method="get" action="manage_students.php">
                    <span class="search-icon"><i class="fas fa-search"></i></span>
                    <input type="search" name="q" placeholder="Search student..." aria-label="Search student" value="<?= escape($search) ?>">
                </form>
                <div class="filter-group">
                    <?php foreach (['all' => 'All', 'Active' => 'Active', 'Pending' => 'Pending', 'Suspended' => 'Suspended'] as $value => $label): ?>
                        <?php $activeClass = ($statusFilter === $value) ? 'active' : ''; ?>
                        <a href="manage_students.php?<?= http_build_query(['q' => $search, 'status' => $value]) ?>" class="filter-pill <?= $activeClass ?>"><?= escape($label) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($feedback !== ''): ?>
                <div class="feedback-message"><?= escape($feedback) ?></div>
            <?php endif; ?>

            <div class="table-card table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Joined</th>
                            <th>Notes Uploaded</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="6" class="empty-row">No students found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $index => $student): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <div class="student-cell">
                                            <span class="student-avatar"><?= escape(strtoupper(substr($student['studentName'], 0, 1))) ?></span>
                                            <div>
                                                <div class="student-name"><?= escape($student['studentName']) ?></div>
                                                <div class="student-email"><?= escape($student['studentEmail']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= escape($student['joined'] ?: '–') ?></td>
                                    <td><?= escape((string) $student['notesUploaded']) ?></td>
                                    <td><span class="badge <?= badgeClass($student['status']) ?>"><?= escape($student['status']) ?></span></td>
                                    <td class="table-action">
                                        <button class="btn btn-view" onclick="window.location.href='manage_students.php?q=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&view_id=<?= urlencode($student['studentID']) ?>'">View</button>
                                        <?php if ($hasStatus): ?>
                                            <form method="post" class="inline-form">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="studentID" value="<?= escape($student['studentID']) ?>">
                                                <input type="hidden" name="newStatus" value="<?= escape($student['status'] === 'Active' ? 'Suspended' : 'Active') ?>">
                                                <button type="submit" class="btn btn-suspend"><?= $student['status'] === 'Active' ? 'Suspend' : 'Activate' ?></button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-suspend disabled" disabled>Update</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
