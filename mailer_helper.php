<?php
// mailer_helper.php
// Sends the OTP email using PHPMailer + Gmail SMTP.
// Setup instructions are in SETUP_GUIDE.md (step 2).

// Manual include (no Composer needed) - PHPMailer files must sit in ./PHPMailer/
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends a 6-digit OTP code to the given email.
 *
 * @param string $toEmail   Recipient email address
 * @param string $toName    Recipient name (optional, for the greeting)
 * @param string $otp       The 6-digit OTP code
 * @param string $role      'student' or 'admin' (only changes wording)
 * @return array            ['success' => bool, 'error' => string|null]
 */
function sendOTPEmail($toEmail, $toName, $otp, $role = 'student') {

    // Credentials are loaded from mailer_config.php (NOT committed to git - see .gitignore)
    require_once __DIR__ . '/mailer_config.php';
    $smtpUsername = SMTP_USERNAME;
    $smtpPassword = SMTP_APP_PASSWORD;
    $fromName     = "UiTMNoteLink Official HQ";

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUsername;
        $mail->Password   = $smtpPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($smtpUsername, $fromName);
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $roleLabel = ($role === 'admin') ? 'admin' : 'student';
        $mail->Subject = "UiTMNoteLink - Your Password Reset OTP";
        $mail->Body    = "
            <div style='font-family: Poppins, Arial, sans-serif;'>
                <h2 style='color:#6b34d9;'>UiTMNoteLink</h2>
                <p>Hi " . htmlspecialchars($toName) . ",</p>
                <p>Here is your OTP code to reset your $roleLabel password:</p>
                <p style='font-size:28px; font-weight:bold; letter-spacing:6px;'>$otp</p>
                <p>This code will expire in 5 minutes. If you did not request this, please ignore this email.</p>
            </div>
        ";
        $mail->AltBody = "Your UiTMNoteLink OTP code is: $otp (expires in 5 minutes)";

        $mail->send();
        return ['success' => true, 'error' => null];

    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}