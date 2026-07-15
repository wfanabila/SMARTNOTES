<?php
// populate_programme_subject.php
// Populates programme_subject by matching subject.subjectCode prefixes for common courses.
// Run: php sql/populate_programme_subject.php
require_once __DIR__ . '/../db_config.php';

$coursePrefixes = [
    'CSC110', 'CSC230', 'CSC264', 'CSC267', 'CSC270'
];

$totalInserted = 0;
foreach ($coursePrefixes as $prefix) {
    // Add entries where subjectCode starts with prefix, set semester=1 by default
    $sql = "INSERT IGNORE INTO programme_subject (programmeCode, subjectID, semester)
            SELECT ?, subjectID, 1 FROM subject WHERE subjectCode LIKE CONCAT(?, '%')";
    $stmt = $conn->prepare($sql);
    if (! $stmt) {
        fwrite(STDERR, "Prepare failed for prefix $prefix: " . $conn->error . "\n");
        continue;
    }
    $stmt->bind_param('ss', $prefix, $prefix);
    if (! $stmt->execute()) {
        fwrite(STDERR, "Execute failed for prefix $prefix: " . $stmt->error . "\n");
        $stmt->close();
        continue;
    }
    $inserted = $stmt->affected_rows;
    $totalInserted += max(0, $inserted);
    $stmt->close();
    fwrite(STDOUT, "Added $inserted rows for $prefix\n");
}

fwrite(STDOUT, "Total inserted: $totalInserted\n");
$conn->close();
