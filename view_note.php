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

$targetID = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_SESSION['studentID']) ? (int)$_SESSION['studentID'] : 1);
$checkUser = $conn->query("SELECT studentID FROM student WHERE studentID = " . $targetID);
$studentID = ($checkUser && $checkUser->num_rows > 0) ? $targetID : 1;

$noteID = isset($_GET['id']) ? (int)$_GET['id'] : 4;
if ($noteID <= 0) {
    die("Error: Invalid resource identifier specified.");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $commentText = trim($_POST['comment_text']);
    $ratingScore = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;

    if (empty($commentText)) {
        $message = "Please write a comment before submitting.";
    } else {
        $insert_stmt = $conn->prepare(
            "INSERT INTO comment (date, studentID, noteID, comments, rating) VALUES (CURRENT_TIMESTAMP, ?, ?, ?, ?)"
        );
        $insert_stmt->bind_param("iisi", $studentID, $noteID, $commentText, $ratingScore);
        
        if ($insert_stmt->execute()) {
            header("Location: view_note.php?id=" . $noteID);
            exit;
        } else {
            $message = "Database Error: Cannot save review. " . $conn->error;
        }
        $insert_stmt->close();
    }
}

$note_query = "SELECT n.*, s.subjectCode, s.subjectName 
               FROM notes n 
               JOIN subject s ON n.subjectID = s.subjectID 
               WHERE n.noteID = ? LIMIT 1";
$stmt = $conn->prepare($note_query);
$stmt->bind_param("i", $noteID);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$note) {
    die("Requested learning resource could not be loaded from the index.");
}

$hasPurchased = false;
$purchase_check = $conn->prepare("SELECT paymentID FROM payment WHERE studentID = ? AND noteID = ? AND paymentStatus = 'Completed' LIMIT 1");
$purchase_check->bind_param("ii", $studentID, $noteID);
$purchase_check->execute();
$purchase_result = $purchase_check->get_result();
if ($purchase_result->num_rows > 0) {
    $hasPurchased = true;
}
$purchase_check->close();

// Check if note is bookmarked
$isBookmarked = false;
$check_status = $conn->prepare("SELECT bookmarkID FROM bookmark WHERE studentID = ? AND noteID = ? LIMIT 1");
$check_status->bind_param("ii", $studentID, $noteID);
$check_status->execute();
if ($check_status->get_result()->num_rows > 0) {
    $isBookmarked = true;
}
$check_status->close();

$comments = [];
$total_rating = 0;
$comment_query = "SELECT c.date, c.comments, c.rating, s.studentName 
                  FROM comment c 
                  JOIN student s ON c.studentID = s.studentID 
                  WHERE c.noteID = ? 
                  ORDER BY c.date DESC";
$stmt = $conn->prepare($comment_query);
$stmt->bind_param("i", $noteID);
$stmt->execute();
$comment_result = $stmt->get_result();
while ($row = $comment_result->fetch_assoc()) {
    $comments[] = $row;
    $total_rating += $row['rating'];
}
$stmt->close();

$review_count = count($comments);
$average_rating = ($review_count > 0) ? round($total_rating / $review_count, 1) : 0.0;

$conn->close();

include_once("sidebar.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($note['title']) ?></title>
    <link href='https://fonts.googleapis.com/css?family=Inter:wght@400;500;600;700;800&display=swap' rel='stylesheet'>
    <link rel='stylesheet' href="sidebar.css">
    <style>
        body { margin: 0; background-color: #ffffff; font-family: 'Inter', sans-serif; color: #1a1a1a; }
        .main { margin-left: 70px; padding: 40px 60px; box-sizing: border-box; transition: margin-left 0.15s ease; }
        .sidebar:hover ~ .main { margin-left: 220px; }
        .workspace-grid { display: grid; grid-template-columns: 1fr 320px; gap: 40px; max-width: 1300px; margin: 0 auto; }
        .content-area { display: flex; flex-direction: column; }
        .note-title-row { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; }
        .note-title-row h1 { font-size: 32px; font-weight: 800; margin: 0; color: #1a1a1a; }
        .badge-premium { background-color: #6D3BD7; color: #ffffff; font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-free { background-color: #E0F2FE; color: #0369A1; font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; }
        .note-subtitle { font-size: 15px; color: #4b5563; margin: 0 0 24px 0; }
        .preview-frame { width: 100%; aspect-ratio: 16 / 9; background-color: #0B131F; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #ffffff; position: relative; overflow: hidden; margin-bottom: 32px; }
        .preview-placeholder-text { text-align: center; padding: 20px; }
        .preview-placeholder-text h2 { font-size: 28px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.02em; }
        .preview-placeholder-text p { font-size: 13px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.1em; }
        .reviews-header { display: flex; align-items: center; gap: 8px; font-size: 20px; font-weight: 700; margin-bottom: 20px; border-top: 1px solid #f3f4f6; padding-top: 24px; }
        .star-rating-row { display: flex; align-items: center; gap: 4px; color: #1a1a1a; font-size: 15px; font-weight: 600; }
        .star { color: #1a1a1a; font-size: 18px; }
        .star.filled { color: #1a1a1a; }
        .star.empty { color: #d1d5db; }
        .review-count-label { color: #4b5563; font-size: 14px; font-weight: 400; }
        .comment-list { display: flex; flex-direction: column; gap: 20px; margin-bottom: 40px; }
        .comment-item { display: flex; gap: 16px; align-items: flex-start; padding-bottom: 20px; border-bottom: 1px solid #f3f4f6; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background-color: #f3f4f6; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; color: #4b5563; flex-shrink: 0; }
        .comment-content h4 { margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: #1a1a1a; }
        .comment-content p { margin: 0; font-size: 14px; color: #4b5563; line-height: 1.5; }
        .feedback-box-title { font-size: 18px; font-weight: 700; margin: 24px 0 4px 0; }
        .feedback-subtext { font-size: 14px; color: #4b5563; margin-bottom: 16px; }
        .rating-input-row { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 6px; margin-bottom: 16px; }
        .rating-input-row input { display: none; }
        .rating-input-row label { font-size: 26px; color: #d1d5db; cursor: pointer; transition: color 0.1s ease; }
        .rating-input-row label:hover, .rating-input-row label:hover ~ label, .rating-input-row input:checked ~ label { color: #1a1a1a; }
        .comment-textarea { width: 100%; min-height: 120px; border: 1px solid #6D3BD7; border-radius: 8px; padding: 16px; font-family: 'Inter', sans-serif; font-size: 14px; outline: none; box-sizing: border-box; resize: vertical; margin-bottom: 20px; }
        .form-actions-row { display: flex; justify-content: flex-end; gap: 12px; }
        .btn-discard { background: transparent; border: 1px solid #d1d5db; color: #1a1a1a; padding: 12px 32px; font-size: 14px; font-weight: 600; border-radius: 6px; cursor: pointer; }
        .btn-submit { background-color: #6D3BD7; border: none; color: #ffffff; padding: 12px 32px; font-size: 14px; font-weight: 600; border-radius: 6px; cursor: pointer; transition: background-color 0.1s; }
        .btn-submit:hover { background-color: #5a2cc2; }
        .sidebar-panel { display: flex; flex-direction: column; gap: 20px; }
        .card-widget { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .widget-subtitle { font-size: 14px; font-weight: 500; color: #000000; margin-bottom: 16px; line-height: 1.4; }
        .widget-price-tag { font-size: 32px; font-weight: 800; color: #1a1a1a; margin-bottom: 16px; }
        .btn-action-purple { width: 100%; background-color: #6D3BD7; color: #ffffff; border: none; padding: 14px; font-size: 14px; font-weight: 600; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; box-sizing: border-box; }
        .btn-action-purple:hover { background-color: #5a2cc2; }
        .btn-action-white { width: 100%; background-color: #ffffff; color: #6D3BD7; border: 1.5px solid #6D3BD7; padding: 14px; font-size: 14px; font-weight: 600; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; box-sizing: border-box; transition: background-color 0.1s; }
        .btn-action-white:hover { background-color: #fcfaff; }
        .btn-action-purple svg, .btn-action-white svg { width: 16px; height: 16px; fill: currentColor; }
        @media (max-width: 1024px) { .workspace-grid { grid-template-columns: 1fr; } .main { padding: 20px; } }
    </style>
</head>
<body>
<div class="main">
    <div class="workspace-grid">
        <div class="content-area">
            <div class="note-title-row">
                <h1><?= htmlspecialchars($note['title']) ?></h1>
                <span class="<?= $note['noteType'] === 'paid' ? 'badge-premium' : 'badge-free' ?>">
                    <?= htmlspecialchars($note['noteType']) ?>
                </span>
            </div>
            <p class="note-subtitle"><?= htmlspecialchars($note['subjectCode'] . ': ' . $note['description']) ?></p>

            <div class="preview-frame">
                <div class="preview-placeholder-text">
                    <h2>TOPIC 1: <?= htmlspecialchars(str_replace(['Slide', 'Chapter 1'], '', $note['title'])) ?></h2>
                    <p><?= htmlspecialchars($note['subjectCode']) ?> – RESPONSIVE WEB APPLICATION</p>
                    <p style="font-size: 10px; margin-top: 20px; color:#4b5563;">EDITION: 2026</p>
                </div>
            </div>

            <div class="reviews-header">
                <span>Reviews</span>
                <div class="star-rating-row">
                    <?php 
                    $floor_rating = floor($average_rating);
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $floor_rating) {
                            echo '<span class="star filled">★</span>';
                        } else {
                            echo '<span class="star empty">★</span>';
                        }
                    }
                    ?>
                    <span><?= number_format($average_rating, 1) ?></span>
                    <span class="review-count-label">(<?= $review_count ?> reviews)</span>
                </div>
            </div>

            <div class="comment-list">
                <?php if ($review_count > 0): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item">
                            <div class="avatar">
                                <?= strtoupper(substr($comment['studentName'], 0, 1)) ?>
                            </div>
                            <div class="comment-content">
                                <h4><?= htmlspecialchars($comment['studentName']) ?></h4>
                                <p><?= htmlspecialchars($comment['comments']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="font-style: italic; color: #6b7280;">No structural feedback yet. Be the first to submit below.</p>
                <?php endif; ?>
            </div>

            <form action="" method="POST">
                <h3 class="feedback-box-title">Give Your Feedback!</h3>
                <p class="feedback-subtext">What would you rate for this note?</p>
                <div class="rating-input-row">
                    <input type="radio" id="star5" name="rating" value="5" checked><label for="star5">★</label>
                    <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                    <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                    <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                    <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                </div>
                <textarea name="comment_text" class="comment-textarea" placeholder="Write your comments..." required></textarea>
                <div class="form-actions-row">
                    <button type="button" class="btn-discard" onclick="window.location.reload();">Discard</button>
                    <button type="submit" name="submit_review" class="btn-submit">Submit</button>
                </div>
            </form>
        </div>

        <div class="sidebar-panel">
            <div class="card-widget">
                <?php if ($hasPurchased || $note['noteType'] === 'free'): ?>
                    <div class="widget-subtitle">You have purchased this notes</div>
                    <a href="<?= htmlspecialchars($note['filePath']) ?>" class="btn-action-white" download>
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4M7 10l5 5 5-5M12 15V3"/></svg> Download PDF
                    </a>
                <?php else: ?>
                    <div class="widget-subtitle">One-Time Purchase</div>
                    <div class="widget-price-tag">RM<?= number_format($note['price'], 2) ?></div>
                    <a href="payment.php?id=<?= $note['noteID'] ?>" class="btn-action-purple">
                        <svg viewBox="0 0 24 24"><path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg> Purchase Now
                        </a>
                <?php endif; ?>
            </div>

            <div class="card-widget">
                <div class="widget-subtitle" style="margin-bottom:8px;">Bookmark</div>
                <button type="button" id="bookmark-btn" class="btn-action-purple" onclick="toggleBookmark(<?= $noteID ?>)" style="<?= $isBookmarked ? 'background-color: #059669;' : '' ?>" data-bookmarked="<?= $isBookmarked ? 'true' : 'false' ?>">
                    <svg viewBox="0 0 24 24"><path d="M17 3H7c-1.1 0-1.99.9-1.99 2L5 21l7-3 7 3V5c0-1.1-.9-2-2-2z" fill="<?= $isBookmarked ? '#ffffff' : 'none' ?>" stroke="currentColor" stroke-width="2"/></svg>
                    <span id="bookmark-text"><?= $isBookmarked ? 'Bookmarked' : 'Bookmark' ?></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Toggle bookmark via AJAX
 * Syncs with dashboard without page reload
 */
function toggleBookmark(noteID) {
    const btn = document.getElementById('bookmark-btn');
    const textSpan = document.getElementById('bookmark-text');
    const isCurrentlyBookmarked = btn.getAttribute('data-bookmarked') === 'true';
    
    // Disable button while processing
    btn.disabled = true;
    btn.style.opacity = '0.6';
    
    fetch('bookmark_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'action=toggle&noteID=' + noteID
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button UI
            btn.setAttribute('data-bookmarked', data.isBookmarked ? 'true' : 'false');
            textSpan.textContent = data.isBookmarked ? 'Bookmarked' : 'Bookmark';
            
            // Update button color
            if (data.isBookmarked) {
                btn.style.backgroundColor = '#059669';
                btn.style.borderColor = '#059669';
                const svg = btn.querySelector('svg path');
                if (svg) svg.setAttribute('fill', '#ffffff');
            } else {
                btn.style.backgroundColor = '';
                btn.style.borderColor = '';
                const svg = btn.querySelector('svg path');
                if (svg) svg.setAttribute('fill', 'none');
            }
            
            // Show brief feedback
            console.log(data.message);
        } else {
            alert('Error: ' + (data.error || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update bookmark. Please try again.');
    })
    .finally(() => {
        // Re-enable button
        btn.disabled = false;
        btn.style.opacity = '1';
    });
}
</script>