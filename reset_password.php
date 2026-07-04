<?php
session_start();
require 'db_config.php';

// Guard: must have verified OTP first
if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['reset_email']) || !isset($_SESSION['reset_role'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['reset_email'];
$role  = $_SESSION['reset_role'];
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword     = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if (strlen($newPassword) < 8) {
        $error_msg = "Password must be at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error_msg = "Passwords do not match.";
    } else {
        $emailEsc = mysqli_real_escape_string($conn, $email);
        // NOTE: kept as plain text to match the existing login.php comparison logic.
        $passEsc = mysqli_real_escape_string($conn, $newPassword);

        if ($role === 'student') {
            mysqli_query($conn, "UPDATE student SET studentPassword='$passEsc' WHERE studentEmail='$emailEsc'");
        } else {
            mysqli_query($conn, "UPDATE admin SET password='$passEsc' WHERE adminEmail='$emailEsc'");
        }

        // Clean up: remove used OTP, clear reset session
        mysqli_query($conn, "DELETE FROM otp_verification WHERE email='$emailEsc' AND role='$role'");
        unset($_SESSION['otp_verified'], $_SESSION['reset_email'], $_SESSION['reset_role'], $_SESSION['reset_name']);

        header("Location: reset_success.php?role=" . $role);
        exit();
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTMNoteLink - Create New Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-login.css">
    <style>
        .password-wrapper {
            position: relative;
        }
        .password-wrapper .custom-input {
            padding-right: 42px;
        }
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 34px;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            color: #9a9690;
            display: flex;
            align-items: center;
        }
        .toggle-password:hover {
            color: #6b34d9;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="text-center mb-3">
                <img class="login-logo" src="https://u.pone.rs/nylrmwij.png" alt="UiTMNoteLink Logo">
            </div>

            <h1 class="login-title">Create New Password</h1>
            <p class="login-subtitle">Please create new password and confirm<br>your new password</p>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger py-2" style="font-size: 0.85rem; font-weight: 500;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form action="reset_password.php" method="post">
                <div class="mb-3 text-start password-wrapper">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" name="newPassword" class="form-control custom-input" id="newPassword" placeholder="Create a password (min 8 chars)" required minlength="8">
                    <button type="button" class="toggle-password" data-target="newPassword" tabindex="-1">
                        <svg class="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg class="eyeOffIcon" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>

                <div class="mb-4 text-start password-wrapper">
                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                    <input type="password" name="confirmPassword" class="form-control custom-input" id="confirmPassword" placeholder="Re-enter your new password" required minlength="8">
                    <button type="button" class="toggle-password" data-target="confirmPassword" tabindex="-1">
                        <svg class="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg class="eyeOffIcon" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>

                <button type="submit" class="btn btn-purple-login w-100">Update your password</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-password').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = btn.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const eyeIcon = btn.querySelector('.eyeIcon');
                const eyeOffIcon = btn.querySelector('.eyeOffIcon');

                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                eyeIcon.style.display = isHidden ? 'none' : '';
                eyeOffIcon.style.display = isHidden ? '' : 'none';
            });
        });
    </script>
</body>
</html>