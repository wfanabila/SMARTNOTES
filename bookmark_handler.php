<?php
/**
 * Bookmark Handler - API endpoint for bookmark operations
 * Handles adding/removing bookmarks via AJAX
 * 
 * Returns JSON response with status and data
 */

header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit();
}

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "smartnotes";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

$studentID = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$noteID = isset($_POST['noteID']) ? (int)$_POST['noteID'] : 0;

if ($noteID <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid note ID']);
    exit();
}

if ($action === 'toggle') {
    // Check if bookmark exists
    $check_stmt = $conn->prepare("SELECT bookmarkID FROM bookmark WHERE studentID = ? AND noteID = ?");
    $check_stmt->bind_param("ii", $studentID, $noteID);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Bookmark exists, remove it
        $delete_stmt = $conn->prepare("DELETE FROM bookmark WHERE studentID = ? AND noteID = ?");
        $delete_stmt->bind_param("ii", $studentID, $noteID);
        
        if ($delete_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'action' => 'removed',
                'message' => 'Bookmark removed',
                'isBookmarked' => false
            ]);
            $delete_stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to remove bookmark']);
        }
    } else {
        // Bookmark doesn't exist, add it
        $add_stmt = $conn->prepare("INSERT INTO bookmark (studentID, noteID, bookmarkDate) VALUES (?, ?, NOW())");
        $add_stmt->bind_param("ii", $studentID, $noteID);
        
        if ($add_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'action' => 'added',
                'message' => 'Bookmark added',
                'isBookmarked' => true
            ]);
            $add_stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add bookmark']);
        }
    }
    
    $check_stmt->close();
    
} elseif ($action === 'check') {
    // Check if note is bookmarked
    $check_stmt = $conn->prepare("SELECT bookmarkID FROM bookmark WHERE studentID = ? AND noteID = ?");
    $check_stmt->bind_param("ii", $studentID, $noteID);
    $check_stmt->execute();
    $isBookmarked = $check_stmt->get_result()->num_rows > 0;
    
    echo json_encode([
        'success' => true,
        'isBookmarked' => $isBookmarked
    ]);
    $check_stmt->close();
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
?>
