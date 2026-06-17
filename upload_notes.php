
<?php
// ====================================================================
// 1. DATABASE CONNECTION
//    Replace these with your actual database details.
// ====================================================================
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "smartnotes";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ====================================================================
// 2. CHECK LOGGED-IN STUDENT
//    Notes.studentID is required (foreign key to Student).
//    Until you have a login page, this falls back to studentID = 1
//    so you can test the upload form. Replace this once login exists:
//
//        session_start();
//        if (!isset($_SESSION['studentID'])) {
//            header("Location: login.php");
//            exit;
//        }
//        $studentID = $_SESSION['studentID'];
// ====================================================================
session_start();
$studentID = $_SESSION['studentID'] ?? 1; // TEMPORARY fallback for testing

// ====================================================================
// 3. HANDLE FORM SUBMISSION
// ====================================================================
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---- Get text fields from the form ----
    $title       = trim($_POST['notestitle']);
    $description = trim($_POST['description']);
    $pricing     = ucfirst($_POST['pricing']);           // "free" or "paid" -> stored as noteType
    $subjectID   = (int) $_POST['subjectid'];   // comes from Subject table
    $price       = ($pricing === 'paid') ? (float) $_POST['price'] : 0.00;

    // ---- Basic validation ----
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

    if (!isset($_FILES['fileUpload']) || $_FILES['fileUpload']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Please choose a file to upload.";
    }

    // ---- Handle the file upload ----
    if (empty($errors)) {

        $file = $_FILES['fileUpload'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error (code " . $file['error'] . ").";
        } else {

            // Allowed file types
            $allowedExt = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'png'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt)) {
                $errors[] = "File type not allowed. Allowed types: " . implode(', ', $allowedExt);
            } else {

                // Folder where files will be stored (must exist & be writable)
                $uploadDir = __DIR__ . '/uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Create a unique filename so files don't overwrite each other
                $safeName = uniqid('note_', true) . '.' . $ext;
                $destination = $uploadDir . $safeName;
                $relativePath = 'uploads/' . $safeName; // stored in DB as filePath

                if (move_uploaded_file($file['tmp_name'], $destination)) {

                    // ---- Save details to the database ----
                    $stmt = $conn->prepare(
                        "INSERT INTO Notes (title, description, filePath, noteType, price, studentID, subjectID)
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );

                    $stmt->bind_param(
                        "ssssdii",
                        $title,
                        $description,
                        $relativePath,
                        $pricing,
                        $price,
                        $studentID,
                        $subjectID
                    );

                    if ($stmt->execute()) {
                        $message = "Note uploaded successfully!";
                    } else {
                        $message = "Database error: " . $stmt->error;
                    }

                    $stmt->close();

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

// ====================================================================
// 4. LOAD SUBJECTS FOR THE DROPDOWN
// ====================================================================
$subjects = [];
$result = $conn->query("SELECT subjectID, subjectCode, subjectName FROM Subject ORDER BY subjectCode");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

$conn->close();

include_once("sidebar.php");
?>

<title>Upload Notes</title>

<link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
<link rel='stylesheet' href="sidebar.css">
<link rel='stylesheet' href="upload_notes.css">
</head>

<body>

<div class="main">
<div class="upload-container">
    <h1>Upload Details</h1>

    <?php if (!empty($message)): ?>
        <div class="form-message">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">

        <!-- FILE -->
        <div class="upload-box">
            <label>Upload File</label><br>
            <input type="file" name="fileUpload" required>
        </div>

        <!-- TITLE -->
        <div class="form-group">
            <label>NOTE TITLE</label>
            <input type="text" name="notestitle" required
                   value="<?= isset($_POST['notestitle']) ? htmlspecialchars($_POST['notestitle']) : '' ?>">
        </div>

        <!-- DESCRIPTION -->
        <div class="form-group">
            <label>DESCRIPTION</label>
            <textarea name="description" required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
        </div>

        <!-- MONETIZATION -->
        <label>MONETIZATION</label><br>

        <label>
            <input type="radio" name="pricing" value="free"
                   <?= (!isset($_POST['pricing']) || $_POST['pricing'] === 'free') ? 'checked' : '' ?>
                   onchange="togglePrice(this)"> Free
        </label>

        <label>
            <input type="radio" name="pricing" value="paid"
                   <?= (isset($_POST['pricing']) && $_POST['pricing'] === 'paid') ? 'checked' : '' ?>
                   onchange="togglePrice(this)"> Paid
        </label>

        <br><br>

        <!-- SUBJECT -->
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

        <!-- PRICE (hidden unless "Paid" is selected) -->
        <div id="priceField" style="display: <?= (isset($_POST['pricing']) && $_POST['pricing'] === 'paid') ? 'block' : 'none' ?>;">
            <label>PRICE</label>
            <input type="number" name="price" step="0.01" placeholder="RM 0.00"
                   value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '' ?>">
        </div>

        <br>

        <!-- SUBMIT -->
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