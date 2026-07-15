<?php
/**
 * Database Setup Script
 * Run this once to add missing columns to the database
 * 
 * INSTRUCTIONS:
 * 1. Open browser: http://localhost/SMARTNOTES/setup.php
 * 2. Click "Run Migration" button
 * 3. Verify the results
 * 4. Delete this file after setup (for security)
 */

$host = "localhost";
$username = "root"; 
$password = "";     
$dbname = "smartnotes";

$setupResults = [];
$hasErrors = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if profilePicture column exists in student table
        $stmt = $pdo->prepare("SHOW COLUMNS FROM student LIKE 'profilePicture'");
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            // Add the column
            $pdo->exec("ALTER TABLE student ADD COLUMN profilePicture VARCHAR(255) DEFAULT NULL AFTER studentEmail");
            $setupResults[] = [
                'status' => 'success',
                'message' => 'Added profilePicture column to student table'
            ];
        } else {
            $setupResults[] = [
                'status' => 'info',
                'message' => 'profilePicture column already exists in student table'
            ];
        }
        
        // Verify the column was added
        $stmt = $pdo->prepare("SHOW COLUMNS FROM student LIKE 'profilePicture'");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $setupResults[] = [
                'status' => 'success',
                'message' => 'Verification: Column exists and is accessible'
            ];
        } else {
            $setupResults[] = [
                'status' => 'error',
                'message' => 'Verification failed: Column not found'
            ];
            $hasErrors = true;
        }
        
    } catch (PDOException $e) {
        $setupResults[] = [
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ];
        $hasErrors = true;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 40px 20px; }
        .container { max-width: 600px; }
        .setup-card { background: white; border-radius: 10px; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .result { padding: 15px; margin: 10px 0; border-radius: 6px; border-left: 4px solid; }
        .result.success { background: #d4edda; border-color: #28a745; color: #155724; }
        .result.error { background: #f8d7da; border-color: #dc3545; color: #721c24; }
        .result.info { background: #d1ecf1; border-color: #17a2b8; color: #0c5460; }
        .warning-banner { background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-card">
            <h1 class="mb-4">Database Setup</h1>
            
            <div class="warning-banner">
                <strong>⚠️ Important:</strong> Delete this file (setup.php) after setup is complete for security reasons.
            </div>

            <?php if (empty($setupResults)): ?>
                <p class="mb-4">This script will add the missing profilePicture column to the student table if it doesn't exist.</p>
                <form method="POST">
                    <button type="submit" name="run_migration" value="1" class="btn btn-primary btn-lg w-100">
                        Run Migration
                    </button>
                </form>
            <?php else: ?>
                <h5 class="mb-3">Setup Results:</h5>
                <?php foreach ($setupResults as $result): ?>
                    <div class="result <?= $result['status'] ?>">
                        <strong><?= ucfirst($result['status']) ?>:</strong> <?= $result['message'] ?>
                    </div>
                <?php endforeach; ?>
                
                <?php if (!$hasErrors): ?>
                    <div class="alert alert-success mt-4">
                        ✓ Setup completed successfully! The profile picture feature is now enabled.
                    </div>
                    <p class="text-muted mt-3">
                        You can now delete this file and refresh the account setting page.
                    </p>
                <?php else: ?>
                    <div class="alert alert-danger mt-4">
                        ✗ Setup encountered errors. Please check the results above.
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="mt-4">
                    <button type="submit" name="run_migration" value="1" class="btn btn-primary w-100">
                        Run Again
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
