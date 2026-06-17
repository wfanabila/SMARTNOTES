<?php
// ====================================================================
// PLATFORM FILE VIEWER ENGINE
// ====================================================================
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "smartnotes";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Database link configuration failed."); }

session_start();
$current_user_id = $_SESSION['studentID'] ?? 2;
$noteID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Gather structural data targets from data system
$stmt = $conn->prepare("SELECT * FROM Notes WHERE noteID = ?");
$stmt->bind_param("i", $noteID);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$note) { die("Target file record missing."); }

// Access authorization verification pipeline logic check
$is_premium = (strtolower($note['noteType']) === 'paid');
$has_access = false;

if (!$is_premium || $note['studentID'] == $current_user_id) {
    $has_access = true;
} else {
    $p_check = $conn->prepare("SELECT 1 FROM Purchases WHERE studentID = ? AND noteID = ?");
    $p_check->bind_param("ii", $current_user_id, $noteID);
    $p_check->execute();
    if ($p_check->get_result()->num_rows > 0) { $has_access = true; }
    $p_check->close();
}
$conn->close();

if (!$has_access) {
    die("Error: Access Denied. You must purchase this file to read it.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewing: <?= htmlspecialchars($note['title']) ?></title>
    <link href="https://fonts.googleapis.com/css?family=Inter:500,600,700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { width: 100%; height: 100%; font-family: 'Inter', sans-serif; background: #1e293b; overflow: hidden; }
        
        /* Layout Framework structure definitions */
        .viewer-container { display: flex; flex-direction: column; width: 100%; height: 100%; }
        .viewer-navbar { background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; height: 64px; }
        .viewer-navbar h1 { font-size: 16px; font-weight: 700; color: #0f172a; }
        
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: #4f46e5; font-weight: 600; font-size: 14px; text-decoration: none; }
        .back-link:hover { color: #4338ca; }
        .content-frame-wrapper { flex-grow: 1; width: 100%; height: calc(100% - 64px); background: #334155; }
        iframe { width: 100%; height: 100%; border: none; }
    </style>
</head>
<body>

<div class="viewer-container">
    <div class="viewer-navbar">
        <a href="view_note.php?id=<?= $note['noteID'] ?>" class="back-link">
            ← Back to Details
        </a>
        <h1>Doc Viewer: <?= htmlspecialchars($note['title']) ?></h1>
        <div style="font-size:12px; color:#64748b; font-weight: 500;">UiTMNoteLink Core Document Engine</div>
    </div>
    
    <div class="content-frame-wrapper">
        <iframe src="<?= htmlspecialchars($note['filePath']) ?>"></iframe>
    </div>
</div>

</body>
</html>