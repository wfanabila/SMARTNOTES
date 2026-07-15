<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "smartnotes";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

session_start();

// ====================================================================
// LOGGED-IN USER (needed by sidebar.php for the top-nav avatar /
// account popup — profile picture, name, email)
// ====================================================================
$user = ['studentName' => '', 'studentEmail' => '', 'profilePicture' => ''];

if (isset($_SESSION['user_id'])) {
    $session_user_id = (int) $_SESSION['user_id'];
    $stmtUser = $conn->prepare("SELECT studentName, studentEmail FROM student WHERE studentID = ?");
    $stmtUser->bind_param("i", $session_user_id);
    $stmtUser->execute();
    $userResult = $stmtUser->get_result();
    if ($userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
    }
    $stmtUser->close();
}

$current_page = 'notes';

// ====================================================================
// CAPTURE FILTER & SEARCH INPUTS
// ====================================================================
$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$max_price   = isset($_GET['max_price']) ? max(0, min(50, (float) $_GET['max_price'])) : 50.00;
$types       = isset($_GET['types']) && is_array($_GET['types'])
    ? array_values(array_intersect($_GET['types'], ['free', 'paid']))
    : []; // Array: ['free', 'paid']
$selectedSubjects = isset($_GET['subjects']) && is_array($_GET['subjects'])
    ? array_values(array_filter($_GET['subjects'], static fn($code) => is_string($code) && $code !== ''))
    : [];

// Populate the programme/subject choices from the database so the filter
// always matches the notes that are actually available.
$availableSubjects = [];
$subjectsResult = $conn->query('SELECT subjectCode FROM subject ORDER BY subjectCode');
if ($subjectsResult) {
    while ($subjectRow = $subjectsResult->fetch_assoc()) {
        $availableSubjects[] = $subjectRow['subjectCode'];
    }
    $subjectsResult->close();
}
$selectedSubjects = array_values(array_intersect($selectedSubjects, $availableSubjects));

// ====================================================================
// DYNAMIC SQL QUERY BUILDING
// ====================================================================
$query = "SELECT n.noteID, n.title, n.description, n.filePath, n.noteType,
                 n.price, n.uploadDate, s.subjectCode, st.studentName
          FROM Notes n
          JOIN Subject s ON n.subjectID = s.subjectID
          JOIN Student st ON n.studentID = st.studentID
          WHERE 1=1";

$params = [];
$types_string = "";

// 1. Search Bar Filter (Matches Title, Description, or Subject Code)
if (!empty($search)) {
    $query .= " AND (n.title LIKE ? OR n.description LIKE ? OR s.subjectCode LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types_string .= "sss";
}

// 2. Subject / programme filter
if (!empty($selectedSubjects)) {
    $placeholders = implode(',', array_fill(0, count($selectedSubjects), '?'));
    $query .= " AND s.subjectCode IN ($placeholders)";
    foreach ($selectedSubjects as $selectedSubject) {
        $params[] = $selectedSubject;
        $types_string .= 's';
    }
}

// 3. Price Range Slider Filter
$query .= " AND n.price <= ?";
$params[] = $max_price;
$types_string .= "d";

// 4. Notes Type Filter (Free / Premium)
if (!empty($types) && count($types) < 2) {
    $chosen_type = $types[0]; // 'free' or 'paid'
    $query .= " AND LOWER(n.noteType) = ?";
    $params[] = strtolower($chosen_type);
    $types_string .= "s";
}

// Order by newest uploads
$query .= " ORDER BY n.uploadDate DESC";

// Execute Statement securely
$stmt = $conn->prepare($query);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types_string, ...$params);
}
$result = false;
if ($stmt && $stmt->execute()) {
    $result = $stmt->get_result();
}

$all_notes = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $all_notes[] = $row;
    }
}
if ($stmt) $stmt->close();
$conn->close();

// Helper palette function for fallback backgrounds
function subjectGradient(string $code): string {
    $palettes = [
        ['#1e293b', '#0f172a'],
        ['#0f172a', '#1e1b4b'], 
        ['#111827', '#030712']
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
    <title>Browse Study Notes – UiTMNoteLink</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            color: #111827;
        }

        .main {
            margin-left: var(--sidebar-width, 260px);
            padding: 28px 40px 40px;
            transition: margin-left 0.3s ease;
        }

        .browse-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 20px;
            flex-wrap: wrap;
            width: 100%;
        }
        .browse-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            width: fit-content;
            margin: 0;
        }
        .search-container {
            position: relative;
            max-width: 320px;
            width: 100%;
        }
        .search-container input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border: 1.5px solid #e5e7eb;
            border-radius: 20px;
            font-size: 14px;
            outline: none;
        }
        .search-container input:focus { border-color: #7c3aed; }
        .search-container svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .page-layout {
            display: grid;
            grid-template-columns: 210px 1fr;
            gap: 18px;
            align-items: start;
        }

        /* Filter Panel */
        .filter-panel {
            background: #f3f0ff;
            border: 1px solid #c4b5fd;
            border-radius: 0;
            padding: 18px;
        }
        .filter-panel h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #111827;
        }
        .filter-section {
            margin-bottom: 18px;
        }
        .filter-section h3 {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #374151;
            margin-bottom: 12px;
        }
        .filter-checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 8px;
            cursor: pointer;
        }
        .filter-checkbox-label input {
            width: 16px;
            height: 16px;
            accent-color: #7c3aed;
        }
        .filter-empty { font-size: 12px; color: #6b7280; }
        
        .price-slider-container {
            margin-top: 8px;
        }
        .price-slider {
            width: 100%;
            accent-color: #7c3aed;
        }
        .price-labels {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #4b5563;
            margin-top: 6px;
            font-weight: 500;
        }

        .apply-btn {
            width: 100%;
            background: #7c3aed;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .apply-btn:hover { background: #6d28d9; }

        /* Grid Section */
        .notes-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
        .note-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .note-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .card-cover {
            height: 110px;
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
        .cover-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            padding: 15px;
            text-align: center;
        }
        .card-body {
            padding: 10px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .card-tags {
            display: flex;
            gap: 6px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .badge {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .badge-code { background: #f3f4f6; color: #4b5563; }
        .badge-premium { background: #7c3aed; color: #fff; }
        .badge-free { background: #6366f1; color: #fff; }

        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
            line-height: 1.4;
        }
        .card-desc {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 14px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .card-footer {
            margin-top: auto;
            border-top: 1px solid #f3f4f6;
            padding-top: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e9d5ff;
            color: #7c3aed;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            overflow: hidden;
            flex-shrink: 0;
        }
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .author-name {
            font-size: 13px;
            font-weight: 500;
            color: #374151;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        @media (max-width: 1200px) {
            .notes-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 900px) {
            .page-layout { grid-template-columns: 1fr; }
            .notes-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .main { margin-left: 0; padding: 20px; }
            .notes-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="main">
    <form method="GET" action="all_notes.php">
        
        <div class="browse-header">
            <h1>Browse Study Notes</h1>
            <div class="search-container">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>

        <div class="page-layout">
            
            <div class="filter-panel">
                <h2>Filters</h2>

                <div class="filter-section">
                    <h3>Programme</h3>
                    <?php if (empty($availableSubjects)): ?>
                        <p class="filter-empty">No subjects available.</p>
                    <?php else: ?>
                        <?php foreach ($availableSubjects as $subjectCode): ?>
                            <label class="filter-checkbox-label">
                                <input type="checkbox" name="subjects[]" value="<?= htmlspecialchars($subjectCode, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($subjectCode, $selectedSubjects, true) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($subjectCode, ENT_QUOTES, 'UTF-8') ?>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="filter-section">
                    <h3>Price Range (RM)</h3>
                    <div class="price-slider-container">
                        <input type="range" name="max_price" class="price-slider" min="0" max="50" step="1" 
                               value="<?= $max_price ?>" oninput="updatePriceLabel(this.value)">
                        <div class="price-labels">
                            <span>RM 0</span>
                            <span id="sliderValueLabel">RM <?= number_format($max_price, 2) ?></span>
                        </div>
                    </div>
                </div>

                <div class="filter-section">
                    <h3>Notes Type</h3>
                    <label class="filter-checkbox-label">
                        <input type="checkbox" name="types[]" value="free" <?= in_array('free', $types) ? 'checked' : '' ?>>
                        FREE
                    </label>
                    <label class="filter-checkbox-label">
                        <input type="checkbox" name="types[]" value="paid" <?= in_array('paid', $types) ? 'checked' : '' ?>>
                        PREMIUM
                    </label>
                </div>

                <button type="submit" class="apply-btn">Apply Filters</button>
            </div>

            <div class="notes-grid">
                <?php if (empty($all_notes)): ?>
                    <div class="empty-state">
                        <p>No study notes match your selection criteria.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($all_notes as $note): ?>
                        <?php 
                            $ext = strtolower(pathinfo($note['filePath'], PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        ?>
                        <div class="note-card" onclick="window.location.href='view_note.php?id=<?= $note['noteID'] ?>'" style="cursor: pointer;">                            
                            <div class="card-cover">
                                <?php if ($isImage): ?>
                                    <img src="<?= htmlspecialchars($note['filePath']) ?>" alt="Note Cover">
                                <?php else: ?>
                                    <div class="cover-fallback" style="<?= subjectGradient($note['subjectCode']) ?>">
                                        <?= htmlspecialchars($note['title']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <div class="card-tags">
                                    <span class="badge badge-code"><?= htmlspecialchars($note['subjectCode']) ?></span>
                                    <?php if (strtolower($note['noteType']) === 'paid'): ?>
                                        <span class="badge badge-premium">PREMIUM</span>
                                    <?php else: ?>
                                        <span class="badge badge-free">FREE</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-title"><?= htmlspecialchars($note['title']) ?></div>
                                <div class="card-desc"><?= htmlspecialchars($note['description']) ?></div>
                                
                                <div class="card-footer">
                                    <div class="avatar">
                                        <?php if (!empty($note['authorPicture'])): ?>
                                            <img src="<?= htmlspecialchars($note['authorPicture']) ?>" alt="<?= htmlspecialchars($note['studentName']) ?>">
                                        <?php else: ?>
                                            <?= strtoupper(substr($note['studentName'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <span class="author-name"><?= htmlspecialchars($note['studentName']) ?></span>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </form>
</div>

<script>
    function updatePriceLabel(val) {
        document.getElementById('sliderValueLabel').innerText = 'RM ' + parseFloat(val).toFixed(2);
    }
</script>
</body>
</html>
