<?php
// Always respond as JSON, never let PHP output HTML error pages that break the frontend
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Catch any fatal error and still return valid JSON instead of a blank response
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Server error: " . $error['message'] . " (line " . $error['line'] . ")"
        ]);
    }
});

session_start();

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "smartnotes";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$email = isset($data['email']) ? trim($data['email']) : '';
$name  = isset($data['name']) ? trim($data['name']) : 'Student';

if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "No email received from Google."]);
    exit;
}

// Check if this email already exists as a student
$stmt = $conn->prepare("SELECT studentID, studentName FROM student WHERE studentEmail = ?");
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Prepare (check) failed: " . $conn->error]);
    exit;
}
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($existingID, $existingName);
$found = $stmt->fetch();
$stmt->close();

if ($found) {
    // Existing student - just log them in
    $_SESSION['studentID'] = $existingID;
    $_SESSION['user_id']   = $existingID;   // matches the session key used by login.php
    $_SESSION['user_name'] = $existingName;
    $_SESSION['role']      = 'student';

    echo json_encode(["status" => "success", "redirect" => "user_dashboard.php"]);
    exit;
}

// New student via Google - auto-create a basic account
$placeholderPassword = bin2hex(random_bytes(8));
$defaultProgramme = "Not set";
$defaultSemester = 1;

$stmt = $conn->prepare(
    "INSERT INTO student (studentName, studentEmail, studentPassword, programme, semester)
     VALUES (?, ?, ?, ?, ?)"
);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Prepare (insert) failed: " . $conn->error]);
    exit;
}
$stmt->bind_param("ssssi", $name, $email, $placeholderPassword, $defaultProgramme, $defaultSemester);

if ($stmt->execute()) {
    $newID = $stmt->insert_id;

    $_SESSION['studentID'] = $newID;
    $_SESSION['user_id']   = $newID;
    $_SESSION['user_name'] = $name;
    $_SESSION['role']      = 'student';

    echo json_encode(["status" => "success", "redirect" => "user_dashboard.php"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to create account: " . $stmt->error]);
}

$stmt->close();
$conn->close();