<?php
session_start();
header('Content-Type: application/json');
require 'db_config.php';
require 'mailer_helper.php';

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_role'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please start again.']);
    exit();
}

$email = mysqli_real_escape_string($conn, $_SESSION['reset_email']);
$role  = $_SESSION['reset_role'];
$name  = $_SESSION['reset_name'] ?? '';

$otp = strval(random_int(100000, 999999));
$expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

mysqli_query($conn, "DELETE FROM otp_verification WHERE email='$email' AND role='$role'");
mysqli_query($conn, "INSERT INTO otp_verification (email, role, otp_code, expires_at) VALUES ('$email', '$role', '$otp', '$expiresAt')");

$sendResult = sendOTPEmail($email, $name, $otp, $role);

echo json_encode([
    'success' => $sendResult['success'],
    'message' => $sendResult['success'] ? 'OTP resent.' : ('Failed to send email: ' . $sendResult['error'])
]);