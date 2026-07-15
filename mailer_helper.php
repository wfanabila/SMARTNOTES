<?php
// mailer_helper.php
// Sends the OTP email using Brevo HTTP API (works on InfinityFree, unlike SMTP sockets).
// No PHPMailer / Composer needed anymore - this uses PHP's built-in cURL.

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

    $roleLabel = ($role === 'admin') ? 'admin' : 'student';

    $htmlBody = "
        <div style='font-family: Poppins, Arial, sans-serif;'>
            <h2 style='color:#6b34d9;'>UiTMNoteLink</h2>
            <p>Hi " . htmlspecialchars($toName) . ",</p>
            <p>Here is your OTP code to reset your $roleLabel password:</p>
            <p style='font-size:28px; font-weight:bold; letter-spacing:6px;'>$otp</p>
            <p>This code will expire in 5 minutes. If you did not request this, please ignore this email.</p>
        </div>
    ";

    $payload = [
        "sender"      => ["name" => SENDER_NAME, "email" => SENDER_EMAIL],
        "to"          => [["email" => $toEmail, "name" => $toName]],
        "subject"     => "UiTMNoteLink - Your Password Reset OTP",
        "htmlContent" => $htmlBody
    ];

    $ch = curl_init("https://api.brevo.com/v3/smtp/email");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "api-key: " . BREVO_API_KEY,
        "content-type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) {
        return ['success' => false, 'error' => "cURL error: $curlErr"];
    }

    if ($httpCode == 201) {
        return ['success' => true, 'error' => null];
    }

    // httpCode 400/401 etc - log the raw response so you can see WHY Brevo rejected it
    error_log("Brevo API error ($httpCode): " . $response);
    return ['success' => false, 'error' => "Brevo API returned HTTP $httpCode: $response"];
}