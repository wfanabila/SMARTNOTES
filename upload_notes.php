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
    // Perfect! The ID exists in the database. Use it.
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
            // If the table is completely clean/empty, insert ID 1 on the fly
            $conn->query("INSERT IGNORE INTO student (studentID, studentName, studentEmail, studentPassword, programme, semester) 
                          VALUES (1, 'Test Student', 'test@test.com', '123', 'Computer Science', 1)");
            $studentID = 1;
        }
    }
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---- Get text fields from the form ----
    $title       = trim($_POST['notestitle']);
    $description = trim($_POST['description']);
    $pricing     = strtolower(trim($_POST['pricing'])); 
    $subjectID   = (int) $_POST['subjectid'];
    $course      = trim($_POST['course']);
    $semester    = (int) $_POST['semester'];
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

    if (empty($course)) {
        $errors[] = "Please select a course.";
    }

    if ($semester < 1 || $semester > 7) {
        $errors[] = "Please select a valid semester (1-7).";
    }

    if ($pricing === 'paid' && $price <= 0) {
        $errors[] = "Please enter a valid price for paid notes.";
    }

    if (!isset($_FILES['fileUpload']) || $_FILES['fileUpload']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Please choose a file to upload.";
    }

    if (empty($errors)) {

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
                $relativePath = 'uploads/' . $safeName; 

                if (move_uploaded_file($file['tmp_name'], $destination)) {

                    $stmt = $conn->prepare(
                        "INSERT INTO notes (title, description, filePath, noteType, price, studentID, subjectID, course, semester, noteStatus)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
                    );

                    if ($stmt === false) {
                        $errors[] = "Prepare statement failed: " . $conn->error;
                    } else {
                        // Forcing variables to correct types
                        $stmt->bind_param(
                            "ssssdiiss",
                            $title,
                            $description,
                            $relativePath,
                            $pricing,
                            $price,
                            $studentID,
                            $subjectID,
                            $course,
                            $semester
                        );

                        if ($stmt->execute()) {
                            header("Location: user_dashboard.php#section-notes");
                            exit;
                        } else {
                            $message = "Database error: " . $stmt->error;
                        }
                        $stmt->close();
                    }

                } else {
                    $errors[] = "Failed to move uploaded file. Check folder permissions.";
                }
            }
        }
    }

    if (!empty($errors)) {
        $message = implode("<br>", $errors);
    }
}

$subjects = [];
$result = $conn->query("SELECT subjectID, subjectCode, subjectName FROM subject ORDER BY subjectCode");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

$conn->close();

include_once("sidebar.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Notes</title>
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
            text-align: center;
            margin-top: 40px;
        }

        input[type="submit"] {
            background: #6D3BD7;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 15px 64px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.15s;
        }

        input[type="submit"]:hover {
            background: #5a2cc2;
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
        }
    </style>
</head>
<body>

<div class="main">
<div class="upload-container">
    <h1>Upload Details</h1>

    <?php if (!empty($message)): ?>
        <div class="form-message" style="color: #dc2626; margin-bottom: 20px; font-weight: 500;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">

        <div class="upload-box">
            <label>Upload File</label><br>
            <input type="file" name="fileUpload" required>
        </div>

        <div class="form-group">
            <label>NOTE TITLE</label>
            <input type="text" name="notestitle" required
                   value="<?= isset($_POST['notestitle']) ? htmlspecialchars($_POST['notestitle']) : '' ?>">
        </div>

        <div class="form-group">
            <label>DESCRIPTION</label>
            <textarea name="description" required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
        </div>

        <div class="monetization-container">
            <div class="section-title">MONETIZATION</div>
            <div class="card-group">
                <label class="monetization-card">
                    <input type="radio" name="pricing" value="free"
                    <?= (!isset($_POST['pricing']) || $_POST['pricing'] === 'free') ? 'checked' : '' ?>
                    onchange="togglePrice(this)">
                    <span class="custom-radio"></span>
                    <div class="card-content">
                        <span class="card-heading">Free</span>
                        <span class="card-subtext">Help fellow students by sharing for free</span>
                    </div>
                </label>

                <label class="monetization-card">
                    <input type="radio" name="pricing" value="paid"
                           <?= (isset($_POST['pricing']) && $_POST['pricing'] === 'paid') ? 'checked' : '' ?>
                           onchange="togglePrice(this)">
                    <span class="custom-radio"></span>
                    <div class="card-content">
                        <span class="card-heading">Paid</span>
                        <span class="card-subtext">Set a price and earn from your hard work</span>
                    </div>
                </label>
            </div>
        </div>

        <label>COURSE</label>
        <select name="course" required>
            <option value="">-- Select Course --</option>
            <option value="CSC110" <?= (isset($_POST['course']) && $_POST['course'] === 'CSC110') ? 'selected' : '' ?>>CSC110</option>
            <option value="CSC230" <?= (isset($_POST['course']) && $_POST['course'] === 'CSC230') ? 'selected' : '' ?>>CSC230</option>
            <option value="CSC264" <?= (isset($_POST['course']) && $_POST['course'] === 'CSC264') ? 'selected' : '' ?>>CSC264</option>
            <option value="CSC267" <?= (isset($_POST['course']) && $_POST['course'] === 'CSC267') ? 'selected' : '' ?>>CSC267</option>
            <option value="CSC270" <?= (isset($_POST['course']) && $_POST['course'] === 'CSC270') ? 'selected' : '' ?>>CSC270</option>
        </select>

        <label>SEMESTER</label>
        <select name="semester" required>
            <option value="">-- Select Semester --</option>
            <?php for ($sem = 1; $sem <= 7; $sem++): ?>
                <option value="<?= $sem ?>" <?= (isset($_POST['semester']) && $_POST['semester'] == $sem) ? 'selected' : '' ?>>Semester <?= $sem ?></option>
            <?php endfor; ?>
        </select>

        <label>SUBJECT CODE</label>
        <select name="subjectid" required>
            <option value="">-- Select Subject --</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['subjectID'] ?>"
                    <?= (isset($_POST['subjectid']) && $_POST['subjectid'] == $subject['subjectID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($subject['subjectCode'] . ' - ' . $subject['subjectName']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <div id="priceField" style="display: <?= (isset($_POST['pricing']) && $_POST['pricing'] === 'paid') ? 'block' : 'none' ?>;">
            <label>PRICE</label>
            <input type="number" name="price" step="0.01" placeholder="RM 0.00"
                   value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '' ?>">
        </div>

        <div class="btn-wrapper">
            <input type="submit" value="Upload Notes">
        </div>

    </form>
</div>
</div>

<script>
    function togglePrice(radio) {
        const priceField = document.getElementById('priceField');
        priceField.style.display = (radio.value === 'paid') ? 'block' : 'none';
    }
</script>

</body>
</html>
