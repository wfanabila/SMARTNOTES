<?php
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 1;
$course = isset($_GET['course']) ? strtoupper(trim($_GET['course'])) : '';

if ($semester < 1 || $semester > 6) {
    $semester = 1;
}

$courseMap = [
    'CSC110' => ['label' => 'CSC110', 'css' => 'css/csc110notes.css', 'file_prefix' => 'csc110'],
    'CSC230' => ['label' => 'CSC230', 'css' => 'css/csc230notes.css', 'file_prefix' => 'csc230'],
    'CSC264' => ['label' => 'CSC264', 'css' => 'css/csc264notes.css', 'file_prefix' => 'csc264'],
    'CSC267' => ['label' => 'CSC267', 'css' => 'css/csc267notes.css', 'file_prefix' => 'csc267'],
    'CSC270' => ['label' => 'CSC270', 'css' => 'css/csc270notes.css', 'file_prefix' => 'csc270'],
];

if ($course === '' || !isset($courseMap[$course])) {
    http_response_code(404);
    echo 'Course not found.';
    exit;
}
$config = $courseMap[$course];
$cssPath = $config['css'];
$filePrefix = $config['file_prefix'];

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'smartnotes';
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$query = "SELECT n.noteID, n.title, n.description, n.filePath, n.noteType, n.price, n.uploadDate, s.subjectCode, s.subjectName
          FROM notes n
          JOIN subject s ON n.subjectID = s.subjectID
          JOIN programme_subject ps ON ps.subjectID = s.subjectID
          WHERE ps.programmeCode = ? AND ps.semester = ?
          ORDER BY n.uploadDate DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('si', $course, $semester);
$stmt->execute();
$result = $stmt->get_result();
$notes = [];
while ($row = $result->fetch_assoc()) {
    $notes[] = $row;
}
$stmt->close();
$subjectStmt = $conn->prepare('SELECT s.subjectCode, s.subjectName FROM programme_subject ps JOIN subject s ON s.subjectID = ps.subjectID WHERE ps.programmeCode = ? AND ps.semester = ? ORDER BY s.subjectCode');
$subjectStmt->bind_param('si', $course, $semester);
$subjectStmt->execute();
$semesterSubjects = $subjectStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$subjectStmt->close();
$conn->close();

function escape_html(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape_html($course) ?> SEM <?= $semester ?> Notes - UiTMNoteLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= escape_html($cssPath) ?>">
</head>
<body>
    <header class="page-header">
        <div class="brand-group">
            <img src="img/logo.PNG" alt="UiTMNoteLink Logo" class="brand-logo">
        </div>
        <nav class="main-nav">
            <a href="landingpage.php" class="main-nav__link">Home</a>
            <a href="<?= escape_html($filePrefix) ?>notes.php" class="main-nav__link">Notes</a>
            <a href="help_center.html" class="main-nav__link">Contributors</a>
            <a href="user_dashboard.php" class="main-nav__link">Dashboard</a>
        </nav>
    </header>

    <main class="page-content">
        <section class="hero-panel">
            <div class="hero-title-group">
                <h1 class="hero-title" id="semester-heading"><?= escape_html($course) ?> SEM <?= $semester ?> Notes</h1>
                <p class="hero-subtitle">Browse notes for <?= escape_html($course) ?> Semester <?= $semester ?>.</p>
            </div>
        </section>

        <section class="recent-section">
            <h2>Subjects for SEM <?= $semester ?></h2>
            <div class="recent-grid" style="margin-bottom: 36px;">
                <?php if (empty($semesterSubjects)): ?>
                    <div class="empty-message">No subjects have been assigned to this semester yet.</div>
                <?php else: ?>
                    <?php foreach ($semesterSubjects as $subject): ?>
                        <article class="recent-card"><div class="recent-card__meta"><?= escape_html($subject['subjectCode']) ?></div><div class="recent-card__meta" style="font-weight:500;color:#475569"><?= escape_html($subject['subjectName']) ?></div></article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <h2>Available notes for SEM <?= $semester ?></h2>
            <div class="recent-grid">
                <?php if (empty($notes)): ?>
                    <div class="empty-message">No notes available for <?= escape_html($course) ?> Semester <?= $semester ?>.</div>
                <?php else: ?>
                    <?php foreach ($notes as $note): ?>
                        <article class="recent-card">
                            <a href="display_note.php?id=<?= urlencode($note['noteID']) ?>" class="recent-card__link">
                                <img src="img/bookmark1.png" alt="<?= escape_html($note['title']) ?>" class="recent-card__thumb">
                                <div class="recent-card__meta"><?= escape_html($note['title']) ?></div>
                                <div class="recent-card__meta" style="font-weight: 500; color: #475569; font-size: 0.95rem; padding-top: 0;"><?= escape_html($note['subjectCode']) ?></div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
