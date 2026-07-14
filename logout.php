<?php
// ob_start() swallows any accidental stray output (BOM, whitespace, notices)
// so header() below can never fail with "headers already sent".
ob_start();

session_start();
$_SESSION = [];
session_destroy();

header("Location: login.php");
exit;