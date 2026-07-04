<?php
// Database connection
$host = "localhost";
$db_user = "root";
$db_pass = ""; 
$db_name = "smartnotes";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM student LIKE 'profilePicture'");
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        // Add the column
        $pdo->exec("ALTER TABLE student ADD COLUMN profilePicture VARCHAR(255) DEFAULT NULL");
        echo "<h2 style='color: green;'>✓ SUCCESS! Column 'profilePicture' added to student table</h2>";
        echo "<p>Refresh your page and try login again.</p>";
    } else {
        echo "<h2 style='color: blue;'>Column 'profilePicture' already exists</h2>";
        echo "<p>Try login again - issue mungkin ada yang lain.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>ERROR: " . $e->getMessage() . "</h2>";
    echo "<p>Pastikan:</p>";
    echo "<ul>";
    echo "<li>Laragon MySQL sedang running</li>";
    echo "<li>Database 'smartnotes' exist</li>";
    echo "<li>Username/password benar (root / no password)</li>";
    echo "</ul>";
}
?>
