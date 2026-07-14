<?php
require_once __DIR__ . '/admin_bootstrap.php';
$activeAdminPage = 'notes';

$columns = [];
$result = $conn->query('SHOW COLUMNS FROM notes');
if ($result) { while ($column = $result->fetch_assoc()) $columns[] = $column['Field']; $result->close(); }
$hasStatus = in_array('noteStatus', $columns, true);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hasStatus) {
    $noteID = (int) ($_POST['noteID'] ?? 0);
    $status = strtolower(trim($_POST['status'] ?? ''));
    if ($noteID > 0 && in_array($status, ['approved', 'rejected', 'pending'], true)) {
        $statement = $conn->prepare('UPDATE notes SET noteStatus = ? WHERE noteID = ?');
        if ($statement) { $statement->bind_param('si', $status, $noteID); $statement->execute(); $statement->close(); $message = 'Note status updated.'; }
    }
}

$filter = strtolower(trim($_GET['status'] ?? 'all'));
if (!in_array($filter, ['all', 'pending', 'approved', 'rejected'], true)) $filter = 'all';
$where = ($hasStatus && $filter !== 'all') ? " WHERE n.noteStatus = '" . $conn->real_escape_string($filter) . "'" : '';
$statusSelect = $hasStatus ? 'n.noteStatus' : "'published' AS noteStatus";
$notes = [];
$sql = "SELECT n.noteID, n.title, n.price, n.filePath, $statusSelect, s.subjectCode, st.studentName
        FROM notes n LEFT JOIN subject s ON s.subjectID = n.subjectID
        LEFT JOIN student st ON st.studentID = n.studentID $where ORDER BY n.noteID DESC";
$result = $conn->query($sql);
if ($result) { while ($row = $result->fetch_assoc()) $notes[] = $row; $result->close(); }
$conn->close();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Manage Notes | UiTM NoteLink</title><link rel="stylesheet" href="css/admin.css"><link rel="stylesheet" href="css/manage_notes.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></head>
<body><div class="layout"><?php include __DIR__ . '/admin_nav.php'; ?><main class="content">
<header class="topbar"><div class="top-nav"><a href="admin.php">Dashboard</a><a href="manage_notes.php" class="active">Manage Notes</a></div><a class="profile-area" href="adminprofile.php"><div class="profile-circle"><?= admin_escape($adminInitial) ?></div><span><?= admin_escape($adminName) ?></span></a></header>
<div class="section-header"><div class="section-title"><h1>Manage Notes</h1><p>Review notes uploaded by students. Changes are saved directly to the database.</p></div></div>
<?php if ($message): ?><p class="admin-notice"><?= admin_escape($message) ?></p><?php endif; ?>
<div class="controls"><div class="filter-group"><?php foreach (['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label): ?><a class="filter-pill <?= $filter === $key ? 'active' : '' ?>" href="manage_notes.php?status=<?= $key ?>"><?= $label ?></a><?php endforeach; ?></div></div>
<div class="table-card"><div class="table-scroll"><table><thead><tr><th>#</th><th>Title</th><th>Subject</th><th>Uploader</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php if (!$notes): ?><tr><td colspan="7">No notes found.</td></tr><?php endif; ?><?php foreach ($notes as $note): ?><tr><td><?= (int)$note['noteID'] ?></td><td><?= admin_escape($note['title']) ?></td><td><?= admin_escape($note['subjectCode'] ?? '-') ?></td><td><?= admin_escape($note['studentName'] ?? 'Unknown') ?></td><td><?= ((float)($note['price'] ?? 0) > 0) ? 'RM ' . number_format((float)$note['price'], 2) : 'Free' ?></td><td><span class="badge <?= admin_escape(strtolower((string)$note['noteStatus'])) ?>"><?= admin_escape(ucfirst((string)$note['noteStatus'])) ?></span></td><td class="table-action"><?php if (!empty($note['filePath'])): ?><a class="btn btn-view" href="<?= admin_escape($note['filePath']) ?>" target="_blank">View</a><?php endif; ?><?php if ($hasStatus): ?><form method="post" class="status-form"><input type="hidden" name="noteID" value="<?= (int)$note['noteID'] ?>"><?php if ($note['noteStatus'] !== 'approved'): ?><button class="btn btn-approve" name="status" value="approved">Approve</button><?php endif; ?><?php if ($note['noteStatus'] !== 'rejected'): ?><button class="btn btn-reject" name="status" value="rejected">Reject</button><?php endif; ?></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div>
</main></div></body></html>
