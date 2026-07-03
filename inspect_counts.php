<?php
$mysqli = new mysqli('localhost', 'root', '', 'smartnotes');
if ($mysqli->connect_error) {
    echo 'CONNECT_FAILED: ' . $mysqli->connect_error . "\n";
    exit(1);
}
$tables = ['notes','Notes','subject','Subject','student','payment'];
foreach ($tables as $t) {
    $res = $mysqli->query("SELECT COUNT(*) AS c FROM $t");
    if ($res) {
        $row = $res->fetch_assoc();
        echo "$t => " . $row['c'] . "\n";
        $res->close();
    } else {
        echo "FAILED $t: " . $mysqli->error . "\n";
    }
}
$mysqli->close();
