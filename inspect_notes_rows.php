<?php
$m = new mysqli('localhost', 'root', '', 'smartnotes');
if ($m->connect_error) {
    echo 'ERR ' . $m->connect_error . "\n";
    exit(1);
}
$tables = ['notes', 'Notes'];
foreach ($tables as $t) {
    $res = $m->query('SELECT * FROM ' . $t . ' LIMIT 20');
    echo "\n=== $t ===\n";
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            foreach ($r as $k => $v) {
                echo $k . ':' . str_replace("\n", '\\n', $v) . ' | ';
            }
            echo "\n";
        }
        $res->close();
    } else {
        echo 'FAIL ' . $m->error . "\n";
    }
}
$m->close();
