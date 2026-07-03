<?php
$mysqli = new mysqli('localhost', 'root', '', 'smartnotes');
if ($mysqli->connect_error) {
    echo 'CONNECT_FAILED: ' . $mysqli->connect_error . "\n";
    exit(1);
}
$res = $mysqli->query('SHOW TABLES');
while ($row = $res->fetch_row()) {
    echo $row[0] . "\n";
}
$tables = ['student', 'notes', 'subject', 'Notes', 'Subject', 'Payment', 'Purchases'];
foreach ($tables as $t) {
    $res = $mysqli->query("SHOW COLUMNS FROM $t");
    if ($res) {
        echo "\nCOLUMNS $t\n";
        while ($c = $res->fetch_assoc()) {
            echo $c['Field'] . ' ' . $c['Type'] . ' ' . $c['Null'] . ' ' . ($c['Key'] ?: '') . "\n";
        }
        $res->close();
    } else {
        echo "\nFAILED SHOW COLUMNS FROM $t: " . $mysqli->error . "\n";
    }
}
$mysqli->close();
