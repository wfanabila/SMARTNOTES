<?php
/** Shared guard and current-admin data for every administrator screen. */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (($_SESSION['role'] ?? '') !== 'admin' || empty($_SESSION['adminID'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db_config.php';

// Notes submitted by students need a persistent approval state.  Older local
// databases did not have this field, so add it once without affecting notes.
$noteStatusCheck = $conn->query("SHOW COLUMNS FROM notes LIKE 'noteStatus'");
if ($noteStatusCheck && $noteStatusCheck->num_rows === 0) {
    $conn->query("ALTER TABLE notes ADD COLUMN noteStatus ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
}
if ($noteStatusCheck) { $noteStatusCheck->close(); }

function admin_escape($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$adminID = (string) $_SESSION['adminID'];
$admin = null;
$statement = $conn->prepare('SELECT * FROM admin WHERE adminID = ? LIMIT 1');
if ($statement) {
    $statement->bind_param('s', $adminID);
    $statement->execute();
    $admin = $statement->get_result()->fetch_assoc();
    $statement->close();
}

if (!$admin) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

$adminName = trim((string) ($admin['adminName'] ?? $_SESSION['user_name'] ?? 'Administrator'));
$adminEmail = trim((string) ($admin['adminEmail'] ?? ''));
$adminInitial = strtoupper(substr($adminName, 0, 1));
$_SESSION['user_name'] = $adminName;
