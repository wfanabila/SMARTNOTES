<?php
/**
 * Search API Endpoint
 * Handles search queries for both admin and student users
 * Returns JSON results of matching notes
 */

header('Content-Type: application/json');
session_start();

$host = "localhost";
$username = "root";
$password = "";
$dbname = "smartnotes";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['success' => true, 'results' => []]);
    exit();
}

$searchQuery = '%' . $conn->real_escape_string($query) . '%';

// Search in notes and subjects
$sql = "SELECT DISTINCT 
            n.noteID, 
            n.title, 
            n.description, 
            n.noteType,
            n.price,
            s.subjectCode, 
            s.subjectName
        FROM notes n
        LEFT JOIN subject s ON n.subjectID = s.subjectID
        WHERE n.title LIKE ? 
           OR n.description LIKE ? 
           OR s.subjectCode LIKE ? 
           OR s.subjectName LIKE ?
        LIMIT 10";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

$stmt->bind_param("ssss", $searchQuery, $searchQuery, $searchQuery, $searchQuery);
$stmt->execute();
$result = $stmt->get_result();

$results = [];
while ($row = $result->fetch_assoc()) {
    $results[] = [
        'noteID' => (int)$row['noteID'],
        'title' => $row['title'],
        'description' => $row['description'],
        'subjectCode' => $row['subjectCode'],
        'subjectName' => $row['subjectName'],
        'noteType' => $row['noteType'],
        'price' => $row['price']
    ];
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'query' => $query,
    'results' => $results,
    'count' => count($results)
]);
?>
