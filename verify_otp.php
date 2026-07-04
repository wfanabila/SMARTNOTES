<?php
session_start();
require 'db_config.php';

// Guard: must have come from forgot_password_student.php or forgot_password_admin.php
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_role'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['reset_email'];
$role  = $_SESSION['reset_role'];
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otpInput = mysqli_real_escape_string($conn, trim($_POST['otp']));
    $emailEsc = mysqli_real_escape_string($conn, $email);

    $query = "SELECT * FROM otp_verification 
              WHERE email='$emailEsc' AND role='$role' AND otp_code='$otpInput' 
              ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if (strtotime($row['expires_at']) < time()) {
            $error_msg = "This OTP code has expired. Please request a new one.";
        } else {
            // Mark as verified, allow password reset
            mysqli_query($conn, "UPDATE otp_verification SET is_verified=1 WHERE id=" . $row['id']);
            $_SESSION['otp_verified'] = true;
            header("Location: reset_password.php");
            exit();
        }
    } else {
        $error_msg = "Invalid OTP code. Please check and try again.";
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTMNoteLink - Check your Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-login.css">
    <style>
        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 24px 0 16px;
        }
        .otp-box {
            width: 45px;
            height: 55px;
            text-align: center;
            font-size: 1.3rem;
            font-weight: 600;
            border: 1.5px solid #c4b5fd;
            border-radius: 10px;
        }
        .otp-box:focus {
            border-color: #6b34d9;
            box-shadow: 0 0 0 3px rgba(107, 52, 217, 0.1);
            outline: none;
        }
        .resend-text {
            font-size: 0.8rem;
            color: #b3a6e0;
            margin-bottom: 20px;
        }
        .resend-text a {
            color: #6b34d9;
            font-weight: 600;
            text-decoration: none;
            pointer-events: none;
        }
        .resend-text a.active {
            pointer-events: auto;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <h1 class="login-title">Check your Email</h1>
            <p class="login-subtitle">
                A six-digit verification code (OTP) has been sent to<br>
                your registered <?= $role === 'admin' ? 'admin' : 'student' ?> email.
            </p>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger py-2" style="font-size: 0.85rem; font-weight: 500;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form action="verify_otp.php" method="post" id="otpForm">
                <input type="hidden" name="otp" id="otpFull">
                <div class="otp-inputs">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                </div>

                <p class="resend-text">
                    Resend email in <span id="countdown">59</span>s
                    &nbsp;<a href="#" id="resendLink">Resend</a>
                </p>

                <button type="submit" class="btn btn-purple-login w-100">Submit</button>
            </form>
        </div>
    </div>

    <script>
        // Auto-advance between OTP boxes
        const boxes = document.querySelectorAll('.otp-box');
        const otpFull = document.getElementById('otpFull');
        const otpForm = document.getElementById('otpForm');

        boxes.forEach((box, i) => {
            box.addEventListener('input', () => {
                box.value = box.value.replace(/[^0-9]/g, '');
                if (box.value && i < boxes.length - 1) boxes[i + 1].focus();
            });
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !box.value && i > 0) boxes[i - 1].focus();
            });
        });

        otpForm.addEventListener('submit', () => {
            otpFull.value = Array.from(boxes).map(b => b.value).join('');
        });

        // Resend countdown
        let seconds = 59;
        const countdownEl = document.getElementById('countdown');
        const resendLink = document.getElementById('resendLink');

        const timer = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                document.querySelector('.resend-text').innerHTML = '';
                resendLink.classList.add('active');
                resendLink.textContent = 'Resend email now';
                document.querySelector('.resend-text').appendChild(resendLink);
            }
        }, 1000);

        resendLink.addEventListener('click', function (e) {
            e.preventDefault();
            if (!resendLink.classList.contains('active')) return;

            fetch('resend_otp.php', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('A new OTP has been sent to your email.');
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to resend OTP.');
                    }
                });
        });
    </script>
</body>
</html>