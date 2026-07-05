<?php
$role = (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'admin' : 'student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTMNoteLink - Password Updated</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-login.css">
    <style>
        .success-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 3px solid #22c55e;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .back-to-login {
            font-size: 0.85rem;
            color: #6b34d9;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 18px;
        }
        .back-to-login:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="text-center mb-3">
                <img class="login-logo" src="https://u.pone.rs/nylrmwij.png" alt="UiTMNoteLink Logo">
            </div>

            <div class="success-icon">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="login-title">
                <?= $role === 'admin' ? 'Your admin password<br>has been updated' : 'Your password has<br>been updated' ?>
            </h1>
            <p class="login-subtitle">You can go back to log in page</p>

            <a href="login.php" class="back-to-login">&#8592; Back to Login Page</a>
        </div>
    </div>
</body>
</html>