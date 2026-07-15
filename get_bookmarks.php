<?php
/**
 * Get Bookmarks API - Returns user's bookmarks as JSON
 * Used by dashboard to fetch and display bookmarks dynamically
 */

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$dbname = "smartnotes";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare(
        "SELECT b.bookmarkID, n.noteID, n.title, n.description, n.filePath, n.noteType, n.price,
                s.subjectCode, s.subjectName
         FROM bookmark b
         JOIN notes n ON b.noteID = n.noteID
         JOIN subject s ON n.subjectID = s.subjectID
         WHERE b.studentID = ?
         ORDER BY b.bookmarkDate DESC"
    );
    $stmt->execute([$user_id]);
    $bookmarks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'bookmarks' => $bookmarks,
        'count' => count($bookmarks)
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
