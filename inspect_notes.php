<?php
$mysqli = new mysqli('localhost', 'root', '', 'smartnotes');
if ($mysqli->connect_error) {
    echo 'CONNECT_FAILED: ' . $mysqli->connect_error . "\n";
    exit(1);
}
$res = $mysqli->query("SELECT n.noteID, n.title, n.noteType, n.price, n.studentID, s.subjectCode FROM notes n JOIN subject s ON n.subjectID = s.subjectID ORDER BY s.subjectCode, n.noteID LIMIT 100");
if (!$res) {
    echo 'QUERY_FAILED: ' . $mysqli->error . "\n";
    exit(1);
}
while ($row = $res->fetch_assoc()) {
    echo implode(' | ', [$row['noteID'], $row['subjectCode'], $row['title'], $row['noteType'], $row['price'], $row['studentID']]) . "\n";
}
$mysqli->close();
