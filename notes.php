<?php
// ====================================================================
// DATABASE CONNECTION
// ====================================================================
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "smartnotes";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

session_start();
$studentID = $_SESSION['studentID'] ?? 1;

// ====================================================================
// HANDLE DELETE (three-dot menu → Delete)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_noteID'])) {
    $deleteID = (int) $_POST['delete_noteID'];

    $stmt = $conn->prepare("SELECT filePath FROM Notes WHERE noteID = ? AND studentID = ?");
    $stmt->bind_param("ii", $deleteID, $studentID);
    $stmt->execute();
    $stmt->bind_result($filePath);
    $stmt->fetch();
    $stmt->close();

    if ($filePath) {
        $fullPath = __DIR__ . '/' . $filePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        $conn->prepare("DELETE FROM Notes WHERE noteID = ? AND studentID = ?")
             ->execute_query([$deleteID, $studentID]);
    }

    header("Location: notes.php"); 
    exit;
}

// ====================================================================
// FETCH THIS STUDENT'S NOTES (newest first)
// ====================================================================
$notes = [];
$stmt = $conn->prepare(
    "SELECT n.noteID, n.title, n.description, n.filePath, n.noteType,
            n.price, n.uploadDate, s.subjectCode
     FROM Notes n
     JOIN Subject s ON n.subjectID = s.subjectID
     WHERE n.studentID = ?
     ORDER BY n.uploadDate DESC"
);
$stmt->bind_param("i", $studentID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notes[] = $row;
}
$stmt->close();
$conn->close();

function subjectGradient(string $code): string {
    $palettes = [
        ['#a78bfa','#7c3aed'], 
        ['#60a5fa','#2563eb'],  
        ['#34d399','#059669'],  
        ['#f472b6','#db2777'],  
        ['#fb923c','#ea580c'],  
        ['#818cf8','#4f46e5'],  
    ];
    $idx = crc32($code) % count($palettes);
    [$a, $b] = $palettes[abs($idx)];
    return "background: linear-gradient(135deg, $a, $b);";
}

include_once("sidebar.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notes – UiTMNoteLink</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f3ff;
            color: #1e1b4b;
        }

        /* ── FIXED OVERLAP: Dynamic padding calculation preventing side navbar intersection ── */
        .main {
            padding: 36px 40px;
            min-height: 100vh;
            display: block;
            position: relative;
            z-index: 1;
            /* Adjusts if your sidebar size varies dynamically */
            margin-left: var(--sidebar-width, 260px); 
            transition: margin-left 0.3s ease;
        }

        .tabs {
            display: flex;
            gap: 28px;
            border-bottom: 1px solid #e2e0f0;
            margin-bottom: 28px;
        }
        .tab {
            padding-bottom: 10px;
            font-size: 15px;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            text-decoration: none;
            border-bottom: 2px solid transparent;
            transition: color .2s, border-color .2s;
        }
        .tab.active {
            color: #7c3aed;
            border-bottom-color: #7c3aed;
            font-weight: 600;
        }

        /* ── RESPONSIVE GRID CONFIGURATION ── */
        .notes-grid {
            display: grid;
            /* Default large screen: exactly 3 columns per row */
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .note-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(109,40,217,.08);
            transition: box-shadow .2s, transform .2s;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        .note-card:hover {
            box-shadow: 0 6px 20px rgba(109,40,217,.14);
            transform: translateY(-2px);
        }

        .card-cover {
            height: 150px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .card-cover .cover-label {
            position: absolute;
            bottom: 10px;
            left: 14px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            text-shadow: 0 1px 4px rgba(0,0,0,.3);
        }

        .card-menu-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,.25);
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            line-index: 1;
            z-index: 2;
        }
        .card-menu-btn:hover { background: rgba(255,255,255,.4); }

        .card-dropdown {
            display: none;
            position: absolute;
            top: 42px;
            right: 10px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,.12);
            z-index: 10;
            min-width: 130px;
            overflow: hidden;
        }
        .card-dropdown.open { display: block; }
        .card-dropdown a,
        .card-dropdown button {
            display: block;
            width: 100%;
            padding: 10px 16px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            text-align: left;
            background: none;
            border: none;
            cursor: pointer;
            color: #374151;
            text-decoration: none;
        }
        .card-dropdown a:hover,
        .card-dropdown button:hover { background: #f3f4f6; }
        .card-dropdown .delete-btn { color: #dc2626; }

        .card-body { 
            padding: 14px 16px 16px; 
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .card-tags {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }
        .tag {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 20px;
            background: #ede9fe;
            color: #6d28d9;
        }

        .card-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e1b4b;
            margin-bottom: 3px;
        }
        .card-desc {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .card-date {
            font-size: 11px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: auto; 
        }

        .new-upload-card {
            border: 2px dashed #c4b5fd;
            border-radius: 14px;
            background: #faf5ff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 240px;
            text-decoration: none;
            transition: background .2s, border-color .2s;
            padding: 20px;
            text-align: center;
        }
        .new-upload-card:hover {
            background: #f3e8ff;
            border-color: #a78bfa;
        }
        .new-upload-card .upload-icon {
            width: 54px;
            height: 54px;
            background: #ede9fe;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .new-upload-card .upload-icon svg { color: #7c3aed; }
        .new-upload-card h3 {
            font-size: 15px;
            font-weight: 700;
            color: #1e1b4b;
        }
        .new-upload-card p {
            font-size: 12px;
            color: #9ca3af;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        /* ── MEDIA RESPONSIVENESS BREAKPOINTS ── */
        @media (max-width: 1100px) {
            .notes-grid {
                grid-template-columns: repeat(2, 1fr); /* Drops to two columns on small desktop scales */
            }
        }

        @media (max-width: 768px) {
            .main {
                margin-left: 70px; /* Reduces side-nav padding block when minimized to prevent overlaps */
                padding: 20px;
            }
            .notes-grid {
                grid-template-columns: 1fr; /* Drops into exactly 1 item per row column */
            }
        }
    </style>
</head>
<body>

<div class="main">
    <div class="tabs">
        <a href="notes.php" class="tab active">My Notes</a>
        <a href="bookmarks.php" class="tab">Bookmarks</a>
    </div>

    <div class="notes-grid">
        <?php if (empty($notes)): ?>
            <div class="empty-state">
                <p>No notes yet. Upload your first one!</p>
            </div>
        <?php else: ?>
            <?php foreach ($notes as $note): ?>
                <?php
                    $ext = strtolower(pathinfo($note['filePath'], PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                    $dateFormatted = date('M j, Y', strtotime($note['uploadDate']));
                ?>
                <div class="note-card">
                    <div class="card-cover" <?= $isImage ? '' : 'style="' . subjectGradient($note['subjectCode']) . '"' ?>>
                        <?php if ($isImage): ?>
                            <img src="<?= htmlspecialchars($note['filePath']) ?>" alt="<?= htmlspecialchars($note['title']) ?>">
                        <?php else: ?>
                            <span class="cover-label"><?= htmlspecialchars($note['subjectCode']) ?></span>
                        <?php endif; ?>

                        <button class="card-menu-btn" onclick="toggleMenu(event, <?= $note['noteID'] ?>)">⋮</button>
                        <div class="card-dropdown" id="menu-<?= $note['noteID'] ?>">
                            <a href="<?= htmlspecialchars($note['filePath']) ?>" target="_blank">View / Download</a>
                            <a href="edit_note.php?id=<?= $note['noteID'] ?>">Edit</a>
                            <form method="POST" onsubmit="return confirm('Delete this note?');">
                                <input type="hidden" name="delete_noteID" value="<?= $note['noteID'] ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="card-tags">
                            <span class="tag"><?= htmlspecialchars($note['subjectCode']) ?></span>
                            <?php if (strtolower($note['noteType']) === 'paid'): ?>
                                <span class="tag" style="background: #fee2e2; color: #ef4444;">PREMIUM</span>
                                <span class="tag" style="background: #fef3c7; color: #d97706;">RM <?= number_format($note['price'], 2) ?></span>
                            <?php else: ?>
                                <span class="tag" style="background: #d1fae5; color: #10b981;">FREE</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-title"><?= htmlspecialchars($note['title']) ?></div>
                        <div class="card-desc"><?= htmlspecialchars($note['description']) ?></div>
                        <div class="card-date">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 5px;">
                                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                            </svg>
                            <?= $dateFormatted ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="upload_notes.php" class="new-upload-card">
            <div class="upload-icon">
                <svg width="26" height="26" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 16V4m0 0-4 4m4-4 4 4"/><path d="M20 16v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2"/>
                </svg>
            </div>
            <h3>New Upload</h3>
            <p>Upload and share your notes with your friends.</p>
        </a>
    </div>
</div>

<script>
    function toggleMenu(event, id) {
        event.stopPropagation();
        const target = document.getElementById('menu-' + id);
        const isOpen = target.classList.contains('open');
        document.querySelectorAll('.card-dropdown').forEach(d => d.classList.remove('open'));
        if (!isOpen) target.classList.add('open');
    }
    document.addEventListener('click', () => {
        document.querySelectorAll('.card-dropdown').forEach(d => d.classList.remove('open'));
    });
</script>
</body>
</html>