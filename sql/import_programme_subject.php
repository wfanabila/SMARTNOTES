<?php
// import_programme_subject.php
// Usage (from project root):
// php sql/import_programme_subject.php

require_once __DIR__ . '/../db_config.php';

$sqlFile = __DIR__ . '/create_programme_subject.sql';
if (!file_exists($sqlFile)) {
    fwrite(STDERR, "SQL file not found: $sqlFile\n");
    exit(2);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    fwrite(STDERR, "Unable to read SQL file: $sqlFile\n");
    exit(3);
}

// Confirm running in CLI
if (PHP_SAPI !== 'cli') {
    fwrite(STDOUT, "Warning: This script is intended to be run from the command line.\n");
}

// Execute the SQL file using multi_query so it can handle multiple statements.
if ($conn->multi_query($sql)) {
    do {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    if ($conn->errno === 0) {
        fwrite(STDOUT, "Import completed successfully.\n");
        exit(0);
    }
}

fwrite(STDERR, "Import failed: " . $conn->error . "\n");
exit(1);
