<?php
/**
 * Database Migration Helper
 * Ensures all required columns exist in the database
 */

function ensureProfilePictureColumn($pdo) {
    try {
        // Check if profilePicture column exists in student table
        $stmt = $pdo->prepare("SHOW COLUMNS FROM student LIKE 'profilePicture'");
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            // Column doesn't exist, create it
            $pdo->exec("ALTER TABLE student ADD COLUMN profilePicture VARCHAR(255) DEFAULT NULL AFTER studentEmail");
            error_log("Migration: Added profilePicture column to student table");
            return ['success' => true, 'message' => 'profilePicture column added'];
        }
        
        return ['success' => true, 'message' => 'profilePicture column already exists'];
    } catch (PDOException $e) {
        error_log("Migration error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Ensure bio column exists in student table
 */
function ensureBioColumn($pdo) {
    try {
        // Check if bio column exists in student table
        $stmt = $pdo->prepare("SHOW COLUMNS FROM student LIKE 'bio'");
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            // Column doesn't exist, create it
            $pdo->exec("ALTER TABLE student ADD COLUMN bio LONGTEXT DEFAULT NULL AFTER profilePicture");
            error_log("Migration: Added bio column to student table");
            return ['success' => true, 'message' => 'bio column added'];
        }
        
        return ['success' => true, 'message' => 'bio column already exists'];
    } catch (PDOException $e) {
        error_log("Migration error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Run all migrations
 */
function runAllMigrations($pdo) {
    $results = [];
    $results['profilePicture'] = ensureProfilePictureColumn($pdo);
    $results['bio'] = ensureBioColumn($pdo);
    return $results;
}
?>
