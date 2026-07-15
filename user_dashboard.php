<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$dbname = "smartnotes";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT studentName, studentEmail FROM student WHERE studentID = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User data could not be retrieved.");
}

$current_page = 'dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_noteID'])) {
    $deleteID = (int) $_POST['delete_noteID'];

    $stmt = $pdo->prepare("SELECT filePath FROM notes WHERE noteID = ? AND studentID = ?");
    $stmt->execute([$deleteID, $user_id]);
    $filePath = $stmt->fetchColumn();

    if ($filePath) {
        $fullPath = __DIR__ . '/' . $filePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        $pdo->prepare("DELETE FROM notes WHERE noteID = ? AND studentID = ?")
            ->execute([$deleteID, $user_id]);
    }

    header("Location: user_dashboard.php#section-notes");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_bookmarkID'])) {
    $removeBM_ID = (int)$_POST['remove_bookmarkID'];
    $stmtRemove = $pdo->prepare("DELETE FROM bookmark WHERE bookmarkID = ? AND studentID = ?");
    $stmtRemove->execute([$removeBM_ID, $user_id]);

    header("Location: user_dashboard.php#section-notes");
    exit;
}

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

$stmt = $pdo->prepare(
    "SELECT n.noteID, n.title, n.description, n.filePath, n.noteType, n.price, n.uploadDate,
            s.subjectCode, s.subjectName,
            COALESCE(AVG(c.rating), 0) AS avgRating,
            COUNT(c.rating) AS reviewCount
     FROM notes n
     JOIN subject s ON n.subjectID = s.subjectID
     LEFT JOIN comment c ON c.noteID = n.noteID
     WHERE n.studentID = ?
     GROUP BY n.noteID, n.title, n.description, n.filePath, n.noteType, n.price, n.uploadDate, s.subjectCode, s.subjectName
     ORDER BY n.uploadDate DESC"
);
$stmt->execute([$user_id]);
$myNotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtBookmarks = $pdo->prepare(
    "SELECT b.bookmarkID, n.noteID, n.title, n.description, n.filePath, n.noteType, n.price,
            s.subjectCode, s.subjectName
     FROM bookmark b
     JOIN notes n ON b.noteID = n.noteID
     JOIN subject s ON n.subjectID = s.subjectID
     WHERE b.studentID = ?
     ORDER BY b.bookmarkDate DESC"
);
$stmtBookmarks->execute([$user_id]);
$myBookmarks = $stmtBookmarks->fetchAll(PDO::FETCH_ASSOC);

$stmtEarnings = $pdo->prepare(
    "SELECT COALESCE(SUM(p.paymentAmount), 0) AS totalEarned,
            COUNT(p.paymentID) AS totalSales
     FROM payment p
     JOIN notes n ON p.noteID = n.noteID
     WHERE n.studentID = ?
       AND n.noteType = 'paid'
       AND p.paymentStatus = 'Completed'"
);
$stmtEarnings->execute([$user_id]);
$earningsRow = $stmtEarnings->fetch(PDO::FETCH_ASSOC);

$pointsEarned = (float) $earningsRow['totalEarned'];
$totalSales   = (int) $earningsRow['totalSales'];

$stmtEarningsWeek = $pdo->prepare(
    "SELECT COALESCE(SUM(p.paymentAmount), 0) AS weekEarned,
            COUNT(p.paymentID) AS weekSales
     FROM payment p
     JOIN notes n ON p.noteID = n.noteID
     WHERE n.studentID = ?
       AND n.noteType = 'paid'
       AND p.paymentStatus = 'Completed'
       AND p.paymentDate >= (NOW() - INTERVAL 7 DAY)"
);
$stmtEarningsWeek->execute([$user_id]);
$earningsWeekRow = $stmtEarningsWeek->fetch(PDO::FETCH_ASSOC);

$weekEarned = (float) $earningsWeekRow['weekEarned'];
$weekSales  = (int) $earningsWeekRow['weekSales'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>User Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
    <link rel="stylesheet" href="css/dashboard.css?v=<?php echo filemtime(__DIR__ . '/css/dashboard.css'); ?>">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
</head>
<body>

    <nav class="topnav">
        <div class="topnav__left">
            <a class="topnav__logo" href="#">
                <span class="topnav__logo-icon">
                    <img src="img/logo.PNG" alt="UiTMNoteLink Logo">
                </span>
            </a>
        </div>

        <div class="topnav__links">
            <a href="landingpage.php" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Home</a>
            <a href="#" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Notes</a>
            <a href="#" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Contributors</a>
            <a href="user_dashboard.php" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Dashboard</a>
        </div>

        <div class="topnav__right">
            <div class="topnav__avatar">
                <?php if (!empty($user['profilePicture'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profilePicture']); ?>" alt="Profile picture" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php include 'sidebar.php'; ?>

    <main class="main">
        <div class="main__container">

            <header class="profile" id="section-overview">
                <h1 class="profile__name"><?php echo htmlspecialchars($user['studentName']); ?>'s Dashboard</h1>

                <p class="profile__bio">
                    <?php
                    if (!empty($user['bio'])) {
                        echo nl2br(htmlspecialchars($user['bio']));
                    } else {
                        echo "No bio added yet. Go to Account Settings to write something about yourself!";
                    }
                    ?>
                </p>
            </header>

            <section class="stats">
                <div class="stat-card stat-card--points">
                    <span class="stat-card__label">Points Earned:</span>
                    <div class="stat-card__amount">
                        RM<?php echo number_format($pointsEarned, 2); ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </div>
                    <p class="stat-card__note">
                        <?php if ($weekSales > 0): ?>
                            You've earned RM<?php echo number_format($weekEarned, 2); ?> from <?php echo $weekSales; ?> sale<?php echo $weekSales === 1 ? '' : 's'; ?> this week. Keep sharing high-quality resources to climb the ranks.
                        <?php elseif ($totalSales > 0): ?>
                            <?php echo $totalSales; ?> total premium note sale<?php echo $totalSales === 1 ? '' : 's'; ?> so far. Keep sharing high-quality resources to climb the ranks.
                        <?php else: ?>
                            No premium note sales yet. Upload and price your notes to start earning.
                        <?php endif; ?>
                    </p>
                </div>

                <div class="stat-card stat-card--uploads">
                    <div class="stat-up-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="19" x2="12" y2="5"></line>
                            <polyline points="5 12 12 5 19 12"></polyline>
                        </svg>
                    </div>
                    <span class="stat-count"><?php echo count($myNotes); ?></span>
                    <h3 class="stat-title">Uploads</h3>
                    <!-- <p class="stat-sub">+3 this month</p> -->
                </div>
            </section>

            <div class="tabs" id="section-notes">
                <button class="tab active" id="tab-mynotes" onclick="switchTab('mynotes')">My Notes</button>
                <button class="tab" id="tab-bookmarks" onclick="switchTab('bookmarks')">Bookmarks</button>
            </div>

            <div class="tab-panel active" id="panel-mynotes">
                <section class="notes-grid">

                    <?php if (empty($myNotes)): ?>
                        <a class="note-card note-card--upload" href="upload_notes.php" style="text-decoration:none; cursor:pointer;">
                            <div class="note-card--upload__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <span class="note-card--upload__title">New Upload</span>
                            <p class="note-card--upload__sub">Upload and share your notes with your friends.</p>
                        </a>
                    <?php else: ?>

                        <?php foreach ($myNotes as $note): ?>
                            <?php
                                $ext = strtolower(pathinfo($note['filePath'], PATHINFO_EXTENSION));
                                $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                $isImage = in_array($ext, $imageExts);
                            ?>
                            <article class="note-card" data-note-id="<?php echo (int) $note['noteID']; ?>" onclick="window.location.href='view_note.php?id=<?php echo (int) $note['noteID']; ?>';" style="cursor: pointer;">
                                <div class="note-card__thumb" <?php echo $isImage ? '' : 'style="' . subjectGradient($note['subjectCode']) . ' display:flex; align-items:center; justify-content:center;"'; ?>>
                                    <?php if ($isImage): ?>
                                        <img src="<?php echo htmlspecialchars($note['filePath']); ?>" alt="<?php echo htmlspecialchars($note['title']); ?>" style="width:100%;height:100%;object-fit:cover;">
                                    <?php else: ?>
                                        <span style="color:#fff; font-weight:700; font-size:18px; text-shadow:0 1px 4px rgba(0,0,0,.3);"><?php echo htmlspecialchars($note['subjectCode']); ?></span>
                                    <?php endif; ?>

                                    <button class="note-card__menu" onclick="event.stopPropagation();">⋮</button>

                                    <div class="note-card__dropdown" onclick="event.stopPropagation();">
                                        <a class="dropdown-item" href="view_note.php?id=<?php echo (int) $note['noteID']; ?>">View Details</a>
                                        <!-- <a class="dropdown-item" href="<?php echo htmlspecialchars($note['filePath']); ?>" target="_blank">View / Download</a> -->
                                        <a class="dropdown-item" href="edit_note.php?id=<?php echo (int) $note['noteID']; ?>">Edit</a>
                                        <form method="POST" onsubmit="return confirm('This will permanently delete this note. Are you sure you want to continue?');">
                                            <input type="hidden" name="delete_noteID" value="<?php echo (int) $note['noteID']; ?>">
                                            <button type="submit" class="dropdown-item btn-delete">Remove</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="note-card__body">
                                    <div class="note-card__tags">
                                        <span class="badge"><?php echo htmlspecialchars($note['subjectCode']); ?></span>
                                        <?php if (strtolower($note['noteType']) === 'paid'): ?>
                                            <span class="badge" style="background:#fee2e2; color:#ef4444;">PREMIUM</span>
                                            <span class="badge" style="background:#fef3c7; color:#d97706;">RM <?php echo number_format($note['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="badge" style="background:#d1fae5; color:#10b981;">FREE</span>
                                        <?php endif; ?>
                                    </div>
                                    <h4 class="note-card__title"><?php echo htmlspecialchars($note['title']); ?></h4>
                                    <p class="note-card__sub"><?php echo htmlspecialchars($note['description']); ?></p>
                                    <div class="note-card__rating" style="display:flex; align-items:center; gap:4px; margin-top:6px;">
                                        <?php
                                            $noteFloorRating = floor((float) $note['avgRating']);
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $noteFloorRating) {
                                                    echo '<span style="color:#f59e0b; font-size:14px;">★</span>';
                                                } else {
                                                    echo '<span style="color:#d1d5db; font-size:14px;">★</span>';
                                                }
                                            }
                                        ?>
                                        <span style="font-size:12px; color:#4b5563; font-weight:600;"><?php echo number_format((float) $note['avgRating'], 1); ?></span>
                                        <span style="font-size:12px; color:#9ca3af;">(<?php echo (int) $note['reviewCount']; ?> review<?php echo ((int) $note['reviewCount']) === 1 ? '' : 's'; ?>)</span>
                                    </div>
                                    <div class="note-card__date">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                        <?php echo date('M j, Y', strtotime($note['uploadDate'])); ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>

                        <a class="note-card note-card--upload" href="upload_notes.php" style="text-decoration:none; cursor:pointer;">
                            <div class="note-card--upload__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <span class="note-card--upload__title">New Upload</span>
                            <p class="note-card--upload__sub">Upload and share your notes with your friends.</p>
                        </a>

                    <?php endif; ?>

                </section>
            </div>

            <div class="tab-panel" id="panel-bookmarks">
                <section class="notes-grid">
                    <?php if (empty($myBookmarks)): ?>
                        <p style="font-style: italic; color: #6b7280; padding: 20px 0; grid-column: 1 / -1; text-align: center;">
                            You haven't bookmarked any notes yet.
                        </p>
                    <?php else: ?>
                        <?php foreach ($myBookmarks as $bm): ?>
                            <?php
                                $ext = strtolower(pathinfo($bm['filePath'], PATHINFO_EXTENSION));
                                $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                $isImage = in_array($ext, $imageExts);
                            ?>
                            <article class="note-card" onclick="window.location.href='view_note.php?id=<?php echo $bm['noteID']; ?>';" style="cursor: pointer;">
                                <div class="note-card__thumb" <?php echo $isImage ? '' : 'style="' . subjectGradient($bm['subjectCode']) . ' display:flex; align-items:center; justify-content:center;"'; ?>>
                                    <?php if ($isImage): ?>
                                        <img src="<?php echo htmlspecialchars($bm['filePath']); ?>" alt="Bookmark Cover" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <span style="color:#fff; font-weight:700; font-size:18px; text-shadow:0 1px 4px rgba(0,0,0,.3);"><?php echo htmlspecialchars($bm['subjectCode']); ?></span>
                                    <?php endif; ?>

                                    <button class="note-card__menu" onclick="event.stopPropagation();">⋮</button>

                                    <div class="note-card__dropdown" onclick="event.stopPropagation();">
                                        <a class="dropdown-item" href="view_note.php?id=<?php echo (int)$bm['noteID']; ?>">View Details</a>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to remove this note from your bookmarks?');">
                                            <input type="hidden" name="remove_bookmarkID" value="<?php echo (int)$bm['bookmarkID']; ?>">
                                            <button type="submit" class="dropdown-item btn-delete">Remove</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="note-card__body">
                                    <div class="note-card__tags">
                                        <span class="badge"><?php echo htmlspecialchars($bm['subjectCode']); ?></span>
                                        <?php if (strtolower($bm['noteType']) === 'paid'): ?>
                                            <span class="badge" style="background:#fee2e2; color:#ef4444;">PREMIUM</span>
                                            <span class="badge" style="background:#fef3c7; color:#d97706;">RM <?php echo number_format($bm['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="badge" style="background:#d1fae5; color:#10b981;">FREE</span>
                                        <?php endif; ?>
                                    </div>
                                    <h4 class="note-card__title"><?php echo htmlspecialchars($bm['title']); ?></h4>
                                    <p class="note-card__sub"><?php echo htmlspecialchars($bm['description']); ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            </div>

        </div>
    </main>

    <script>
    const menuButtons = document.querySelectorAll('.note-card__menu');
    menuButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.stopPropagation();

            const parentThumb = this.parentElement;
            const currentDropdown = parentThumb.querySelector('.note-card__dropdown');

            document.querySelectorAll('.note-card__dropdown').forEach(dropdown => {
                if (dropdown !== currentDropdown) {
                    dropdown.classList.remove('show');
                }
            });

            currentDropdown.classList.toggle('show');
        });
    });

    window.addEventListener('click', function () {
        document.querySelectorAll('.note-card__dropdown').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    });

    function switchTab(tabName) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.getElementById('tab-' + tabName).classList.add('active');

        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('panel-' + tabName).classList.add('active');

        setSidebarActive('nav-' + tabName);
    }

    function setSidebarActive(id) {
        ['nav-overview', 'nav-mynotes', 'nav-bookmarks'].forEach(navId => {
            const el = document.getElementById(navId);
            if(el) el.classList.remove('active');
        });
        const activeEl = document.getElementById(id);
        if(activeEl) activeEl.classList.add('active');
    }

    function navigateTo(section) {
        if (section === 'overview') {
            setSidebarActive('nav-overview');
            const el = document.getElementById('section-overview');
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else if (section === 'mynotes') {
            switchTab('mynotes');
            document.getElementById('section-notes').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else if (section === 'bookmarks') {
            switchTab('bookmarks');
            document.getElementById('section-notes').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    /**
     * Refresh bookmarks from the server
     * Called on page load to sync with any bookmark changes from view_note.php
     */
    function refreshBookmarks() {
        fetch('get_bookmarks.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const bookmarksGrid = document.querySelector('#panel-bookmarks .notes-grid');
                    if (bookmarksGrid) {
                        // If no bookmarks, show message
                        if (data.bookmarks.length === 0) {
                            bookmarksGrid.innerHTML = '<p style="font-style: italic; color: #6b7280; padding: 20px 0; grid-column: 1 / -1; text-align: center;">You haven\'t bookmarked any notes yet.</p>';
                        } else {
                            // Generate bookmark HTML
                            let html = '';
                            data.bookmarks.forEach(bm => {
                                const ext = bm.filePath.split('.').pop().toLowerCase();
                                const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                const isImage = imageExts.includes(ext);
                                const thumbStyle = isImage ? '' : getGradientStyle(bm.subjectCode);
                                const thumbContent = isImage 
                                    ? `<img src="${escapeHtml(bm.filePath)}" alt="Bookmark Cover" style="width: 100%; height: 100%; object-fit: cover;">`
                                    : `<span style="color:#fff; font-weight:700; font-size:18px; text-shadow:0 1px 4px rgba(0,0,0,.3);">${escapeHtml(bm.subjectCode)}</span>`;
                                
                                const badgeType = bm.noteType.toLowerCase() === 'paid' 
                                    ? `<span class="badge" style="background:#fee2e2; color:#ef4444;">PREMIUM</span><span class="badge" style="background:#fef3c7; color:#d97706;">RM ${parseFloat(bm.price).toFixed(2)}</span>`
                                    : `<span class="badge" style="background:#d1fae5; color:#10b981;">FREE</span>`;

                                html += `
                                    <article class="note-card" onclick="window.location.href='view_note.php?id=${bm.noteID}';" style="cursor: pointer;">
                                        <div class="note-card__thumb" style="${thumbStyle} display:flex; align-items:center; justify-content:center;">
                                            ${thumbContent}
                                            <button class="note-card__menu" onclick="event.stopPropagation();">⋮</button>
                                            <div class="note-card__dropdown" onclick="event.stopPropagation();">
                                                <a class="dropdown-item" href="view_note.php?id=${bm.noteID}">View Details</a>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to remove this note from your bookmarks?');">
                                                    <input type="hidden" name="remove_bookmarkID" value="${bm.bookmarkID}">
                                                    <button type="submit" class="dropdown-item btn-delete">Remove</button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="note-card__body">
                                            <div class="note-card__tags">
                                                <span class="badge">${escapeHtml(bm.subjectCode)}</span>
                                                ${badgeType}
                                            </div>
                                            <h4 class="note-card__title">${escapeHtml(bm.title)}</h4>
                                            <p class="note-card__sub">${escapeHtml(bm.description)}</p>
                                        </div>
                                    </article>
                                `;
                            });
                            bookmarksGrid.innerHTML = html;
                            
                            // Re-attach menu button listeners
                            attachMenuListeners();
                        }
                    }
                }
            })
            .catch(error => console.error('Error refreshing bookmarks:', error));
    }

    /**
     * Helper function to escape HTML special characters
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Helper function to get gradient style for subject code
     */
    function getGradientStyle(code) {
        const palettes = [
            ['#a78bfa','#7c3aed'],
            ['#60a5fa','#2563eb'],
            ['#34d399','#059669'],
            ['#f472b6','#db2777'],
            ['#fb923c','#ea580c'],
            ['#818cf8','#4f46e5'],
        ];
        
        // Simple hash function
        let hash = 0;
        for (let i = 0; i < code.length; i++) {
            hash = ((hash << 5) - hash) + code.charCodeAt(i);
            hash = hash & hash;
        }
        
        const idx = Math.abs(hash) % palettes.length;
        const [a, b] = palettes[idx];
        return `background: linear-gradient(135deg, ${a}, ${b});`;
    }

    /**
     * Reattach event listeners to menu buttons after DOM update
     */
    function attachMenuListeners() {
        const menuButtons = document.querySelectorAll('.note-card__menu');
        menuButtons.forEach(button => {
            button.addEventListener('click', function (event) {
                event.stopPropagation();
                const parentThumb = this.parentElement;
                const currentDropdown = parentThumb.querySelector('.note-card__dropdown');
                document.querySelectorAll('.note-card__dropdown').forEach(dropdown => {
                    if (dropdown !== currentDropdown) {
                        dropdown.classList.remove('show');
                    }
                });
                currentDropdown.classList.toggle('show');
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setSidebarActive('nav-overview');
        // Refresh bookmarks on page load to sync with view_note.php bookmarks
        refreshBookmarks();
    });

    // Also refresh bookmarks when page becomes visible (e.g., returning from another tab)
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            refreshBookmarks();
        }
    });
    </script>

</body>
</html>