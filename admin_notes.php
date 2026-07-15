<?php
require_once __DIR__ . '/admin_bootstrap.php';
$search = trim($_GET['search'] ?? '');
$subject = trim($_GET['subject'] ?? '');
$type = strtolower(trim($_GET['type'] ?? ''));
$where = []; $params = []; $types = '';
if ($search !== '') { $where[] = '(n.title LIKE ? OR n.description LIKE ? OR s.subjectCode LIKE ? OR s.subjectName LIKE ?)'; $term = "%$search%"; array_push($params, $term, $term, $term, $term); $types .= 'ssss'; }
if ($subject !== '') { $where[] = 's.subjectCode = ?'; $params[] = $subject; $types .= 's'; }
if (in_array($type, ['free','paid'], true)) { $where[] = 'n.noteType = ?'; $params[] = $type; $types .= 's'; }
$sql = "SELECT n.noteID,n.title,n.description,n.filePath,n.noteType,n.price,s.subjectCode,s.subjectName,st.studentName FROM notes n JOIN subject s ON s.subjectID=n.subjectID JOIN student st ON st.studentID=n.studentID" . ($where ? ' WHERE '.implode(' AND ', $where) : '') . ' ORDER BY n.uploadDate DESC, n.noteID DESC';
$stmt = $conn->prepare($sql); if ($params) $stmt->bind_param($types, ...$params); $stmt->execute(); $notes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
$subjects = $conn->query('SELECT subjectCode,subjectName FROM subject ORDER BY subjectCode')->fetch_all(MYSQLI_ASSOC); $conn->close();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Browse Study Notes | UiTM NoteLink</title><link rel="stylesheet" href="css/admin_notes.css"></head><body>
<div class="layout">
	<?php include __DIR__ . '/admin_nav.php'; ?>
	<main class="content">
		<header class="topbar">
			<div class="top-nav">
				<a href="admin_dashboard.php">Dashboard</a>
				<a class="active" href="admin_notes.php">Notes</a>
			</div>
			<a class="profile-area" href="adminprofile.php"><div class="profile-circle"><?= admin_escape($adminInitial) ?></div><span><?= admin_escape($adminName) ?></span></a>
		</header>

		<div class="title"><h1>Browse Study Notes</h1><form><input name="search" value="<?= admin_escape($search) ?>" placeholder="Search subject or note..."><button>Search</button></form></div>
		<div class="body"><form class="filters" method="get"><h2>Filters</h2><label>Subject<select name="subject"><option value="">All subjects</option><?php foreach($subjects as $s): ?><option value="<?= admin_escape($s['subjectCode']) ?>" <?= $subject===$s['subjectCode']?'selected':'' ?>><?= admin_escape($s['subjectCode'].' — '.$s['subjectName']) ?></option><?php endforeach; ?></select></label><label>Note type<select name="type"><option value="">Free & Premium</option><option value="free" <?= $type==='free'?'selected':'' ?>>Free</option><option value="paid" <?= $type==='paid'?'selected':'' ?>>Premium</option></select></label><input type="hidden" name="search" value="<?= admin_escape($search) ?>"><button>Apply filters</button></form><section class="notes"><?php if(!$notes): ?><p>No notes match your search.</p><?php endif; ?><?php foreach($notes as $i=>$note): ?><article><img src="img/bookmark<?= ($i%3)+1 ?>.png" alt=""><div><small><?= admin_escape($note['subjectCode']) ?> · <?= strtoupper(admin_escape($note['noteType'])) ?></small><h3><?= admin_escape($note['title']) ?></h3><p><?= admin_escape($note['description'] ?? '') ?></p><footer><?= admin_escape($note['studentName']) ?> <b><?= $note['noteType']==='paid'?'RM '.number_format((float)$note['price'],2):'Free' ?></b></footer></div></article><?php endforeach; ?></section></div>
	</main>
</div>
</body></html>
