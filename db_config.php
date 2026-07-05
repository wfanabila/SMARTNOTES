<?php
// db_config.php
// Shared database connection - include this in every forgot-password file.

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "smartnotes";

$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}