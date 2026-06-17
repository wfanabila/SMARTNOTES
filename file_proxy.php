<?php
// ====================================================================
// DATABASE CONFIGURATION & INITIALIZATION
// ====================================================================
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "smartnotes";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

session_start();
// Default login session context mock for "Siti Aminah" (studentID: 2)
$_SESSION['studentID'] = $_SESSION['studentID'] ?? 2;
$current_user_id = $_SESSION['studentID'];

$noteID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch individual asset profile details
$stmt = $conn->prepare("SELECT n.*, s.subjectCode, st.studentName FROM Notes n 
                        JOIN Subject s ON n.subjectID = s.subjectID 
                        JOIN Student st ON n.studentID = st.studentID 
                        WHERE n.noteID = ?");
$stmt->bind_param("i", $noteID);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$note) { die("The requested study notes asset could not be located."); }

// Check purchase records table
$has_purchased = false;
$p_stmt = $conn->prepare("SELECT 1 FROM Payment WHERE studentID = ? AND noteID = ? AND paymentStatus = 'Success'");
if ($p_stmt) {
    $p_stmt->bind_param("ii", $current_user_id, $noteID);
    $p_stmt->execute();
    if ($p_stmt->get_result()->num_rows > 0) { $has_purchased = true; }
    $p_stmt->close();
}

// Handle Bookmarking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_bookmark'])) {
    $d_stmt = $conn->prepare("INSERT INTO Download (studentID, noteID) VALUES (?, ?)");
    $d_stmt->bind_param("ii", $current_user_id, $noteID);
    $d_stmt->execute();
    $d_stmt->close();
    echo "<script>alert('Note bookmarked successfully!'); window.location.href='view_note.php?id=$noteID';</script>";
    exit;
}

// Handle Submitting Review Comments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $r_stmt = $conn->prepare("INSERT INTO Comment (studentID, noteID, date) VALUES (?, ?, NOW())");
        $r_stmt->bind_param("ii", $current_user_id, $noteID);
        $r_stmt->execute();
        $r_stmt->close();
    }
    header("Location: view_note.php?id=" . $noteID);
    exit;
}

// Fetch Reviews/Comments
$reviews = [];
$r_fetch = $conn->prepare("SELECT c.date, s.studentName FROM Comment c JOIN Student s ON c.studentID = s.studentID WHERE c.noteID = ? ORDER BY c.date DESC");
$r_fetch->bind_param("i", $noteID);
$r_fetch->execute();
$res = $r_fetch->get_result();
while($row = $res->fetch_assoc()) { $reviews[] = $row; }
$r_fetch->close();

$conn->close();

$is_premium = (strtolower($note['noteType']) === 'paid');
$is_accessible = (!$is_premium || $has_purchased || $note['studentID'] == $current_user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($note['title']) ?> - Details</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #ffffff; color: #111827; }
        
        /* Navigation Responsive Bar */
        .navbar-top { display: flex; justify-content: space-between; align-items: center; padding: 20px 80px; background: #ffffff; border-bottom: 1px solid #f3f4f6; flex-wrap: wrap; gap: 15px; }
        .nav-logo { font-size: 20px; font-weight: 800; color: #7c3aed; text-decoration: none; }
        .nav-links { display: flex; gap: 32px; list-style: none; }
        .nav-links a { text-decoration: none; color: #1f2937; font-weight: 500; font-size: 15px; }
        .nav-links a.active { color: #7c3aed; font-weight: 600; border-bottom: 2px solid #7c3aed; padding-bottom: 4px; }
        .user-icon { width: 36px; height: 36px; background: #111827; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; }

        /* Workspace Grid Container Layout */
        .workspace-grid { display: grid; grid-template-columns: 1fr 300px; gap: 48px; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        .note-title-row h1 { font-size: 28px; font-weight: 700; color: #000; display: inline-flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .badge-premium { background: #7c3aed; color: #ffffff; font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 12px; text-transform: uppercase; }
        .badge-free { background: #6366f1; color: #ffffff; font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 12px; text-transform: uppercase; }
        .note-subtitle { font-size: 15px; color: #374151; margin-top: 6px; margin-bottom: 24px; }
        
        /* Reading Canvas Display Frame */
        .preview-window { width: 100%; height: 460px; background: #0f172a; border-radius: 4px; overflow: hidden; margin-bottom: 32px; border: 1px solid #e5e7eb; position: relative; }
        .paywall-overlay { position: absolute; inset: 0; background: linear-gradient(135deg, #1e293b, #0f172a); display: flex; flex-direction: column; align-items: center; justify-content: center; color: #fff; text-align: center; padding: 20px; }
        .preview-iframe { width: 100%; height: 100%; border: none; }

        /* Action Right Sidebar Cards */
        .side-panel-card { background: #ffffff; border: 1px solid #e5e7eb; padding: 24px; margin-bottom: 20px; border-radius: 4px; }
        .side-panel-card h3 { font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 8px; }
        .side-panel-card .price-amount { font-size: 32px; font-weight: 700; color: #000000; margin-bottom: 16px; }
        
        .action-button { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 14px; border-radius: 6px; font-weight: 600; font-size: 15px; cursor: pointer; text-decoration: none; border: none; transition: background 0.15s; text-align: center; }
        .btn-filled-purple { background: #7c3aed; color: #ffffff; }
        .btn-filled-purple:hover { background: #6d28d9; }
        .btn-outlined-purple { background: transparent; border: 1.5px solid #7c3aed; color: #7c3aed; }
        .btn-outlined-purple:hover { background: #fdfaff; }

        /* Comments Engine Output Styles */
        .reviews-summary-bar { display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; margin-top: 32px; margin-bottom: 16px; flex-wrap: wrap; gap: 10px; }
        .reviews-summary-bar h2 { font-size: 18px; font-weight: 700; color: #000; }
        .stars-row { color: #111827; font-size: 18px; display: inline-flex; gap: 2px; align-items: center; }
        
        .comment-row { display: flex; gap: 16px; padding: 16px 0; border-bottom: 1px solid #f3f4f6; align-items: start; }
        .avatar-circle { width: 36px; height: 36px; border-radius: 50%; background: #f3e8ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; flex-shrink: 0; }
        .comment-text-container { flex-grow: 1; }
        .comment-user-name { font-size: 14px; font-weight: 700; color: #000; margin-bottom: 4px; }
        .comment-message-body { font-size: 14px; color: #374151; line-height: 1.5; }

        /* Feedback Submit Form Card Area */
        .feedback-entry-card { margin-top: 40px; }
        .feedback-entry-card h3 { font-size: 18px; font-weight: 700; margin-bottom: 4px; color: #000; }
        .feedback-entry-card p { font-size: 14px; color: #4b5563; margin-bottom: 16px; }

        .star-rating-form-group { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 6px; margin-bottom: 20px; }
        .star-rating-form-group input { display: none; }
        .star-rating-form-group label { font-size: 36px; color: #d1d5db; cursor: pointer; transition: color 0.1s; }
        .star-rating-form-group input:checked ~ label,
        .star-rating-form-group label:hover,
        .star-rating-form-group label:hover ~ label { color: #111827; }

        .feedback-entry-card textarea { width: 100%; padding: 16px; border: 1px solid #c4b5fd; border-radius: 8px; font-family: inherit; font-size: 14px; outline: none; margin-bottom: 20px; min-height: 120px; }
        .form-buttons-flex { display: flex; justify-content: flex-end; gap: 16px; }
        .btn-cancel { background: #ffffff; border: 1px solid #d1d5db; color: #374151; padding: 10px 28px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-submit { background: #7c3aed; color: #ffffff; border: none; padding: 10px 28px; border-radius: 6px; font-weight: 600; cursor: pointer; }

        /* ====================================================================
           RESPONSIVE BREAKPOINTS (MEDIA QUERIES)
           ==================================================================== */
        @media (max-width: 992px) {
            .navbar-top { padding: 20px 40px; }
            .workspace-grid { grid-template-columns: 1fr; gap: 32px; }
            /* Shifts widget layout to top or handles clean reading stack */
            .side-panel-card { margin-bottom: 16px; }
        }

        @media (max-width: 600px) {
            .navbar-top { padding: 15px 20px; flex-direction: column; text-align: center; }
            .nav-links { gap: 16px; padding-left: 0; margin: 10px 0; }
            .note-title-row h1 { font-size: 22px; }
            .preview-window { height: 320px; }
            .form-buttons-flex { width: 100%; flex-direction: column; }
            .btn-cancel, .btn-submit { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

    <nav class="navbar-top">
        <a href="all_notes.php" class="nav-logo">UiTMNoteLink</a>
        <ul class="nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="all_notes.php" class="active">Notes</a></li>
            <li><a href="contributors.php">Contributors</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
        </ul>
        <div class="user-icon">SA</div>
    </nav>

    <div class="workspace-grid">
        
        <div>
            <div class="note-title-row">
                <h1>
                    <?= htmlspecialchars($note['title']) ?>
                    <span class="<?= $is_premium ? 'badge-premium' : 'badge-free' ?>">
                        <?= $is_premium ? 'PREMIUM' : 'FREE' ?>
                    </span>
                </h1>
                <p class="note-subtitle">Topic 1: <?= htmlspecialchars($note['description']) ?></p>
            </div>

            <div class="preview-window">
                <?php if ($is_accessible): ?>
                    <iframe src="file_proxy.php?id=<?= $note['noteID'] ?>" class="preview-iframe"></iframe>
                <?php else: ?>
                    <div class="paywall-overlay">
                        <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">Premium Document Locked</h2>
                        <p style="font-size: 13px; opacity: 0.8; max-width: 280px;">Please complete the purchase step to unlock access to the study guide.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="reviews-summary-bar">
                <h2>Reviews</h2>
                <div class="stars-row">
                    <span style="color: #111827; font-size: 20px; margin-right: 6px;">★★★☆☆</span>
                    <span style="font-weight: 700; font-size: 15px;">3.8</span>
                    <span style="color: #6b7280; font-size: 13px; margin-left: 4px;">(<?= count($reviews) ?> reviews)</span>
                </div>
            </div>

            <div class="comments-output-feed">
                <div class="comment-row">
                    <div class="avatar-circle" style="background: #fee2e2;"><span style="color: #ef4444;">WN</span></div>
                    <div class="comment-text-container">
                        <div class="comment-user-name">Wafa Nabila</div>
                        <div class="comment-message-body">Your notes are very organized and easy to understand. Thanks for sharing!</div>
                    </div>
                </div>

                <div class="comment-row">
                    <div class="avatar-circle" style="background: #d1fae5;"><span style="color: #10b981;">M</span></div>
                    <div class="comment-text-container">
                        <div class="comment-user-name">Muhammad</div>
                        <div class="comment-message-body">This is one of the clearest note sets I've seen so far.</div>
                    </div>
                </div>

                <?php foreach ($reviews as $rev): ?>
                    <div class="comment-row">
                        <div class="avatar-circle"><span><?= strtoupper(substr($rev['studentName'], 0, 1)) ?></span></div>
                        <div class="comment-text-container">
                            <div class="comment-user-name"><?= htmlspecialchars($rev['studentName']) ?></div>
                            <div class="comment-message-body">Awesome file content! Reviewed on platform.</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="feedback-entry-card">
                <h3>Give Your Feedback!</h3>
                <p>What would you rate for this note?</p>
                
                <form method="POST" action="">
                    <div class="star-rating-form-group">
                        <input type="radio" id="star5" name="rating" value="5" required><label for="star5">★</label>
                        <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                        <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                        <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                        <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                    </div>

                    <textarea name="comment" placeholder="Write your comments..." required></textarea>
                    
                    <div class="form-buttons-flex">
                        <button type="reset" class="btn-cancel">Discard</button>
                        <button type="submit" name="submit_review" class="btn-submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="side-panel-card">
                <?php if ($is_premium && !$has_purchased && $note['studentID'] != $current_user_id): ?>
                    <h3>One-Time Purchase</h3>
                    <div class="price-amount">RM<?= number_format($note['price'], 2) ?></div>
                    <a href="checkout.php?id=<?= $note['noteID'] ?>" class="action-button btn-filled-purple">
                        <span style="margin-right: 8px;">$</span> Purchase Now
                    </a>
                <?php else: ?>
                    <h3>You have purchased this note</h3>
                    <a href="file_proxy.php?id=<?= $note['noteID'] ?>&download=true" class="action-button btn-outlined-purple" style="border-color:#d1d5db; color:#111827; margin-top: 10px;">
                        <span style="margin-right: 8px;">📥</span> Download PDF
                    </a>
                <?php endif; ?>
            </div>

            <div class="side-panel-card">
                <h3>Bookmark</h3>
                <form method="POST" action="">
                    <button type="submit" name="action_bookmark" class="action-button btn-filled-purple">
                        <span style="margin-right: 8px;">🔖</span> Bookmark
                    </button>
                </form>
            </div>
        </div>

    </div>

</body>
</html>