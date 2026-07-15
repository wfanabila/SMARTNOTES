<?php
// fetch_subjects.php
// Returns JSON list of subjects for a given course and semester.
require_once __DIR__ . '/../db_config.php';

$course = strtoupper(trim($_GET['course'] ?? ''));
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;

header('Content-Type: application/json; charset=utf-8');

if ($course === '') {
    echo json_encode(['error' => 'course_required']);
    exit;
}

$check = $conn->query("SHOW TABLES LIKE 'programme_subject'");
$subjects = [];
if ($check && $check->num_rows > 0) {
    $stmt = $conn->prepare('SELECT s.subjectID, s.subjectCode, s.subjectName FROM programme_subject ps JOIN subject s ON s.subjectID = ps.subjectID WHERE ps.programmeCode = ?' . ($semester > 0 ? ' AND ps.semester = ?' : '') . ' ORDER BY s.subjectCode');
    if ($stmt) {
        if ($semester > 0) {
            $stmt->bind_param('si', $course, $semester);
        } else {
            $stmt->bind_param('s', $course);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $subjects[] = $r;
        }
        $stmt->close();
    }
}

// fallback if none found or programme_subject missing
if (empty($subjects)) {
    if ($semester > 0) {
        $stmt2 = $conn->prepare('SELECT DISTINCT s.subjectID, s.subjectCode, s.subjectName FROM notes n JOIN subject s ON n.subjectID = s.subjectID WHERE n.course = ? AND n.semester = ? ORDER BY s.subjectCode');
        if ($stmt2) {
            $stmt2->bind_param('si', $course, $semester);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            while ($r = $res2->fetch_assoc()) {
                $subjects[] = $r;
            }
            $stmt2->close();
        }
    }
    if (empty($subjects)) {
        $like = $course . '%';
        $stmt3 = $conn->prepare('SELECT subjectID, subjectCode, subjectName FROM subject WHERE subjectCode LIKE ? ORDER BY subjectCode');
        if ($stmt3) {
            $stmt3->bind_param('s', $like);
            $stmt3->execute();
            $res3 = $stmt3->get_result();
            while ($r = $res3->fetch_assoc()) {
                $subjects[] = $r;
            }
            $stmt3->close();
        }
    }
}

echo json_encode(['subjects' => $subjects]);
$conn->close();
