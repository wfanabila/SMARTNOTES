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

$targetID = isset($_SESSION['studentID']) ? (int)$_SESSION['studentID'] : 1;

$checkUser = $conn->query("SELECT studentID FROM student WHERE studentID = " . $targetID);

if ($checkUser && $checkUser->num_rows > 0) {
    $studentID = $targetID;
} else {
    $checkFallback = $conn->query("SELECT studentID FROM student WHERE studentID = 1");

    if ($checkFallback && $checkFallback->num_rows > 0) {
        $studentID = 1;
    } else {
        $anyUser = $conn->query("SELECT studentID FROM student LIMIT 1");
        if ($anyUser && $anyUser->num_rows > 0) {
            $row = $anyUser->fetch_assoc();
            $studentID = (int)$row['studentID'];
        } else {
            $conn->query("INSERT IGNORE INTO student (studentID, studentName, studentEmail, studentPassword, programme, semester) 
                          VALUES (1, 'Test Student', 'test@test.com', '123', 'Computer Science', 1)");
            $studentID = 1;
        }
    }
}

$noteID = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($noteID <= 0) {
    die("No note specified to edit.");
}

$stmt = $conn->prepare("SELECT * FROM notes WHERE noteID = ? AND studentID = ?");
$stmt->bind_param("ii", $noteID, $studentID);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$note) {
    die("Note not found, or you don't have permission to edit it.");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim($_POST['notestitle']);
    $description = trim($_POST['description']);
    $pricing     = strtolower(trim($_POST['pricing']));
    $subjectID   = (int) $_POST['subjectid'];
    $price       = ($pricing === 'paid') ? (float) $_POST['price'] : 0.00;

    $errors = [];

    if (empty($title)) {
        $errors[] = "Note title is required.";
    }

    if (empty($description)) {
        $errors[] = "Description is required.";
    }

    if ($subjectID <= 0) {
        $errors[] = "Please select a subject.";
    }

    if ($pricing === 'paid' && $price <= 0) {
        $errors[] = "Please enter a valid price for paid notes.";
    }

    $relativePath = $note['filePath'];

    if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] !== UPLOAD_ERR_NO_FILE) {

        $file = $_FILES['fileUpload'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error (code " . $file['error'] . ").";
        } else {

            $allowedExt = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'png'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt)) {
                $errors[] = "File type not allowed. Allowed types: " . implode(', ', $allowedExt);
            } else {

                $uploadDir = __DIR__ . '/uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $safeName = uniqid('note_', true) . '.' . $ext;
                $destination = $uploadDir . $safeName;
                $newRelativePath = 'uploads/' . $safeName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {

                    // remove the old file now that the new one is safely saved
                    $oldFullPath = __DIR__ . '/' . $note['filePath'];
                    if (file_exists($oldFullPath)) {
                        unlink($oldFullPath);
                    }

                    $relativePath = $newRelativePath;

                } else {
                    $errors[] = "Failed to move uploaded file. Check folder permissions.";
                }
            }
        }
    }

    if (empty($errors)) {

        $stmt = $conn->prepare(
            "UPDATE notes
             SET title = ?, description = ?, filePath = ?, noteType = ?, price = ?, subjectID = ?
             WHERE noteID = ? AND studentID = ?"
        );

        if ($stmt === false) {
            $errors[] = "Prepare statement failed: " . $conn->error;
        } else {
            $stmt->bind_param(
                "ssssdiii",
                $title,
                $description,
                $relativePath,
                $pricing,
                $price,
                $subjectID,
                $noteID,
                $studentID
            );

            if ($stmt->execute()) {
                header("Location: user_dashboard.php#section-notes");
                exit;
            } else {
                $message = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    if (!empty($errors)) {
        $message = implode("<br>", $errors);
    }

    $note['title']       = $title;
    $note['description']  = $description;
    $note['noteType']    = $pricing;
    $note['subjectID']   = $subjectID;
    $note['price']       = $price;
}

$subjects = [];
$result = $conn->query("SELECT subjectID, subjectCode, subjectName FROM subject ORDER BY subjectCode");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

$userStmt = $conn->prepare("SELECT studentName, studentEmail, profilePicture FROM student WHERE studentID = ?");
$userStmt->bind_param("i", $studentID);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

$current_page = 'mynotes';

$conn->close();

include_once("sidebar.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Note</title>
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
    <link rel='stylesheet' href="sidebar.css">
    <style>
        .sidebar:hover ~ .main {
            margin-left: 220px;
        }

        .main {
            font-family: 'Inter', sans-serif;
            margin-top: 0;
        }

        .upload-container {
            max-width: 850px;
            margin: auto;
            padding: 48px 40px;
        }

        .upload-container h1 {
            font-size: 36px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 36px;
            color: #1a1a1a;
        }

        .upload-container label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #1a1a1a;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .upload-container input[type="text"],
        .upload-container input[type="number"],
        .upload-container textarea,
        .upload-container select {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #e0ddd6;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            color: #1a1a1a;
            background: #ffffff;
            transition: border-color 0.15s;
            outline: none;
        }

        .upload-container input[type="text"]:focus,
        .upload-container input[type="number"]:focus,
        .upload-container textarea:focus,
        .upload-container select:focus {
            border-color: #6D3BD7;
        }

        .upload-container textarea {
            resize: vertical;
            min-height: 110px;
        }

        .current-file {
            font-size: 13px;
            color: #6b6860;
            margin-top: 10px;
        }

        .current-file a {
            color: #6D3BD7;
            font-weight: 600;
            text-decoration: none;
        }

        .current-file a:hover {
            text-decoration: underline;
        }

        .upload-box {
            border: 2px dashed #c4b5fd;
            background: #fdfbff;
            border-radius: 12px;
            padding: 32px;
            text-align: center;
            margin-bottom: 28px;
            transition: border-color 0.15s, background 0.15s;
        }

        .upload-box:hover {
            border-color: #6D3BD7;
            background: #f5f0ff;
        }

        .upload-box label {
            font-size: 15px;
            font-weight: 600;
            color: #6D3BD7;
            text-transform: none;
            letter-spacing: 0;
            margin-bottom: 12px;
        }

        .upload-box input[type="file"] {
            margin-top: 10px;
            font-size: 14px;
            color: #6b6860;
        }

        .btn-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            margin-top: 40px;
        }

        input[type="submit"] {
            background: #6D3BD7;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 15px 48px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.15s;
        }

        input[type="submit"]:hover {
            background: #5a2cc2;
        }

        .btn-discard-page {
            background: #ffffff;
            color: #dc2626;
            border: 1.5px solid #dc2626;
            border-radius: 10px;
            padding: 14px 48px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }

        .btn-discard-page:hover {
            background: #fef2f2;
        }

        .monetization-container {
            margin: 24px 0 32px 0;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #1a1a1a;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            display: block;
            margin-bottom: 12px;
        }

        .card-group {
            display: flex !important;
            flex-direction: row !important;
            gap: 16px;
            flex-wrap: wrap;
        }

        .upload-container .monetization-card {
            display: flex !important;
            flex-direction: row !important;
            align-items: flex-start !important;
            border: 1.5px solid #e0ddd6; 
            border-radius: 12px;
            padding: 20px;
            min-width: 280px;
            flex: 1;
            cursor: pointer;
            transition: border-color 0.15s, background-color 0.15s;
            background-color: #ffffff;
            box-sizing: border-box;
            text-transform: none !important;
            margin-bottom: 0px !important;
        }

        .monetization-card:hover {
            border-color: #c4b5fd;
            background-color: #fdfbff;
        }

        .monetization-card:has(input[type="radio"]:checked) {
            border-color: #6D3BD7 !important; 
            background-color: #f5f0ff;
        }

        .monetization-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .custom-radio {
            position: relative;
            display: inline-block !important;
            min-width: 20px;
            max-width: 20px;
            height: 20px;
            border: 2px solid #c4b5fd;
            border-radius: 50%;
            margin-right: 14px;
            margin-top: 2px;
            background: #fff;
            box-sizing: border-box;
        }

        .monetization-card input[type="radio"]:checked + .custom-radio {
            border-color: #6D3BD7;
        }

        .monetization-card input[type="radio"]:checked + .custom-radio::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #6D3BD7;
        }

        .card-content {
            display: flex !important;
            flex-direction: column !important;
        }

        .card-heading {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #1a1a1a;
            margin-bottom: 4px;
            text-transform: none !important; 
            letter-spacing: 0 !important;
            display: block !important;
        }

        .card-subtext {
            font-size: 13px !important;
            font-weight: 400 !important;
            color: #6b6860;
            line-height: 1.4;
            text-transform: none !important;
            letter-spacing: 0 !important;
            display: block !important;
        }

        @media (max-width: 640px) {
            .card-group {
                flex-direction: column !important;
            }
            .upload-container {
                padding: 24px 20px;
            }
            .btn-wrapper {
                flex-direction: column;
                width: 100%;
            }
            input[type="submit"], .btn-discard-page {
                width: 100%;
            }
        }

        /* Discard changes confirmation modal */
        .discard-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(26, 26, 26, 0.45);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .discard-overlay.show {
            display: flex;
        }

        .discard-modal {
            background: #ffffff;
            border-radius: 14px;
            padding: 28px 28px 24px;
            max-width: 380px;
            width: 100%;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
            font-family: 'Inter', sans-serif;
        }

        .discard-modal h3 {
            font-size: 18px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .discard-modal p {
            font-size: 14px;
            color: #6b6860;
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .discard-modal__actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .discard-btn {
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.15s;
        }

        .discard-btn--keep {
            background: #f3effe;
            color: #6D3BD7;
        }

        .discard-btn--keep:hover {
            background: #e5d9fb;
        }

        .discard-btn--discard {
            background: #dc2626;
            color: #ffffff;
        }

        .discard-btn--discard:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>

<div class="main">
<div class="upload-container">
    <h1>Edit Note</h1>

    <?php if (!empty($message)): ?>
        <div class="form-message" style="color: #dc2626; margin-bottom: 20px; font-weight: 500;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" id="editNoteForm">

        <div class="upload-box">
            <label>Replace File (optional)</label><br>
            <input type="file" name="fileUpload">
            <p class="current-file">
                Current file:
                <a href="<?= htmlspecialchars($note['filePath']) ?>" target="_blank">
                    <?= htmlspecialchars(basename($note['filePath'])) ?>
                </a>
                — leave this empty to keep it.
            </p>
        </div>

        <div class="form-group">
            <label>NOTE TITLE</label>
            <input type="text" name="notestitle" required
                   value="<?= htmlspecialchars($note['title']) ?>">
        </div>

        <div class="form-group">
            <label>DESCRIPTION</label>
            <textarea name="description" required><?= htmlspecialchars($note['description']) ?></textarea>
        </div>

        <div class="monetization-container">
            <div class="section-title">MONETIZATION</div>
            <div class="card-group">
                <label class="monetization-card">
                    <input type="radio" name="pricing" value="free"
                    <?= (strtolower($note['noteType']) === 'free') ? 'checked' : '' ?>
                    onchange="togglePrice(this)">
                    <span class="custom-radio"></span>
                    <div class="card-content">
                        <span class="card-heading">Free</span>
                        <span class="card-subtext">Help fellow students by sharing for free</span>
                    </div>
                </label>

                <label class="monetization-card">
                    <input type="radio" name="pricing" value="paid"
                           <?= (strtolower($note['noteType']) === 'paid') ? 'checked' : '' ?>
                           onchange="togglePrice(this)">
                    <span class="custom-radio"></span>
                    <div class="card-content">
                        <span class="card-heading">Paid</span>
                        <span class="card-subtext">Set a price and earn from your hard work</span>
                    </div>
                </label>
            </div>
        </div>

        <label>SUBJECT CODE</label>
        <select name="subjectid" required>
            <option value="">-- Select Subject --</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['subjectID'] ?>"
                    <?= ($note['subjectID'] == $subject['subjectID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($subject['subjectCode'] . ' - ' . $subject['subjectName']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <div id="priceField" style="display: <?= (strtolower($note['noteType']) === 'paid') ? 'block' : 'none' ?>;">
            <label>PRICE</label>
            <input type="number" name="price" step="0.01" placeholder="RM 0.00"
                   value="<?= htmlspecialchars($note['price']) ?>">
        </div>

        <div class="btn-wrapper">
            <button type="button" class="btn-discard-page" id="pageDiscardBtn">Discard</button>
            <input type="submit" value="Save Changes">
        </div>

    </form>
</div>
</div>

<div class="discard-overlay" id="discardOverlay">
    <div class="discard-modal">
        <h3>Discard changes?</h3>
        <p>You've made changes to this note that haven't been saved yet. If you leave now, those changes will be lost.</p>
        <div class="discard-modal__actions">
            <button type="button" class="discard-btn discard-btn--keep" id="discardKeepBtn">Keep Editing</button>
            <button type="button" class="discard-btn discard-btn--discard" id="discardConfirmBtn">Discard Changes</button>
        </div>
    </div>
</div>

<script>
    function togglePrice(radio) {
        const priceField = document.getElementById('priceField');
        priceField.style.display = (radio.value === 'paid') ? 'block' : 'none';
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('editNoteForm');
    const overlay = document.getElementById('discardOverlay');
    const keepBtn = document.getElementById('discardKeepBtn');
    const confirmBtn = document.getElementById('discardConfirmBtn');
    const pageDiscardBtn = document.getElementById('pageDiscardBtn');

    let formChanged = false;
    let isSubmitting = false;
    let pendingHref = null;

    if (form) {
        form.addEventListener('input', () => { formChanged = true; });
        form.addEventListener('change', () => { formChanged = true; });
        form.addEventListener('submit', () => { isSubmitting = true; });
    }

    function openDiscardModal(href) {
        pendingHref = href || "user_dashboard.php#section-notes";
        overlay.classList.add('show');
    }

    function closeDiscardModal() {
        overlay.classList.remove('show');
        pendingHref = null;
    }

    keepBtn.addEventListener('click', closeDiscardModal);

    confirmBtn.addEventListener('click', () => {
        formChanged = false; 
        const href = pendingHref;
        closeDiscardModal();
        if (href) window.location.href = href;
    });

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeDiscardModal();
    });

    pageDiscardBtn.addEventListener('click', () => {
        if (formChanged) {
            openDiscardModal("user_dashboard.php#section-notes");
        } else {
            window.location.href = "user_dashboard.php#section-notes";
        }
    });

    document.addEventListener('click', function (e) {
        const link = e.target.closest('a[href]');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href === '#' || href.startsWith('#') || link.target === '_blank') return;

        if (!formChanged) return;

        e.preventDefault();
        openDiscardModal(href);
    }, true);

    window.addEventListener('beforeunload', function (e) {
        if (formChanged && !isSubmitting) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});
</script>

</body>
</html>