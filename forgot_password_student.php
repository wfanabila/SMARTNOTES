<?php
session_start();
require 'db_config.php';
require 'mailer_helper.php';

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    $query = "SELECT * FROM student WHERE studentEmail='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Generate 6-digit OTP
        $otp = strval(random_int(100000, 999999));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        // Remove any old OTP for this email+role, then insert fresh one
        mysqli_query($conn, "DELETE FROM otp_verification WHERE email='$email' AND role='student'");
        mysqli_query($conn, "INSERT INTO otp_verification (email, role, otp_code, expires_at) VALUES ('$email', 'student', '$otp', '$expiresAt')");

        $sendResult = sendOTPEmail($email, $user['studentName'], $otp, 'student');

        if ($sendResult['success']) {
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_role']  = 'student';
            $_SESSION['reset_name']  = $user['studentName'];
            header("Location: verify_otp.php");
            exit();
        } else {
            $error_msg = "Failed to send OTP email. Please try again. (" . $sendResult['error'] . ")";
        }
    } else {
        $error_msg = "No student account found with that email.";
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTMNoteLink - Password Recovery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-login.css">
</head>
<body>

    <a href="login.php" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" style="stroke: currentColor; stroke-width: 1px;">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
        </svg>
        Back
    </a>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="text-center mb-3">
                <img class="login-logo" src="https://u.pone.rs/nylrmwij.png" alt="UiTMNoteLink Logo">
            </div>

            <h1 class="login-title">Password Recovery</h1>
            <p class="login-subtitle">Please enter your registered email below<br>and we'll send you an OTP code</p>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger py-2" style="font-size: 0.85rem; font-weight: 500;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form action="forgot_password_student.php" method="post">
                <div class="mb-4 text-start">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control custom-input" id="email" placeholder="you@example.com" required>
                </div>

                <button type="submit" class="btn btn-purple-login w-100">Send OTP code</button>
            </form>
        </div>
    </div>

</body>
</html>