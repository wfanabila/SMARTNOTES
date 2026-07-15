<?php
session_start();
$error_msg = "";
$submittedRole = isset($_POST['role']) ? $_POST['role'] : 'student';
$submittedEmail = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
$isAdmin = ($submittedRole === 'admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $db_user = "root";
    $db_pass = ""; 
    $db_name = "smartnotes"; 

    $conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = $_POST['role']; 

    if ($role === 'student') {
        $query = "SELECT * FROM student WHERE studentEmail='$email' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            if ($password === $user['studentPassword']) {
                $_SESSION['user_id'] = $user['studentID'];
                $_SESSION['studentID'] = $user['studentID'];
                $_SESSION['user_name'] = $user['studentName'];
                $_SESSION['role'] = 'student';
                
                header("Location: user_dashboard.php");
                exit();
            } else {
                $error_msg = "Invalid student email or password!";
            }
        } else {
            $error_msg = "Invalid student email or password!";
        }
    } 
    else if ($role === 'admin') {
        $query = "SELECT * FROM admin WHERE adminEmail='$email' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $admin = mysqli_fetch_assoc($result);

            if ($password === $admin['password']) {
                $_SESSION['user_id'] = $admin['adminID'];
                $_SESSION['adminID'] = $admin['adminID'];
                $_SESSION['user_name'] = $admin['adminName'];
                $_SESSION['role'] = 'admin';

                header("Location: adminprofile.php");
                exit();
            } else {
                $error_msg = "Invalid admin email or password!";
            }
        } else {
            $error_msg = "Invalid admin email or password!";
        }
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTMNoteLink - Log In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">  
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-login.css">

    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>

    <style>
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 12px;
            border: 1.5px solid #e0ddd6;
            border-radius: 10px;
            background: #ffffff;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
        }
        .google-btn:hover {
            background: #f9f9f9;
            border-color: #c4b5fd;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0;
            color: #9a9690;
            font-size: 13px;
        }
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e0ddd6;
        }

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
    
    <a href="front_page.html" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16" style="stroke: currentColor; stroke-width: 1px;">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
        </svg>
        Back
    </a>

    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="text-center mb-4">
                <img class="login-logo" src="https://u.pone.rs/nylrmwij.png" alt="UiTMNoteLink Logo">
            </div>

            <h1 class="login-title">Welcome back</h1>
            <p class="login-subtitle">Sign in to your account</p>


            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger py-2" style="font-size: 0.85rem; font-weight: 500;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div class="role-toggle mb-4">
                <button type="button" class="role-btn <?= $isAdmin ? 'active' : '' ?>" id="btnAdmin">Admin</button>
                <button type="button" class="role-btn <?= !$isAdmin ? 'active' : '' ?>" id="btnStudent">Student</button>
            </div>

            <!-- Google Sign-In + divider: Student only -->
            <div id="googleSection" style="display: <?= $isAdmin ? 'none' : '' ?>;">
                <button type="button" id="googleSignInBtn" class="google-btn">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="18" height="18" alt="Google">
                    Continue with Google
                </button>

                <div class="divider"><span>or</span></div>
            </div>

            <form action="login.php" method="post">
                
                <input type="hidden" name="role" id="roleInput" value="<?= $isAdmin ? 'admin' : 'student' ?>">
                
                <div class="mb-3 text-start">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control custom-input" id="email"
                           placeholder="<?= $isAdmin ? 'Admin Email' : 'Student Email' ?>"
                           value="<?= $submittedEmail ?>" required>
                </div>
                
                <div class="mb-4 text-start password-wrapper">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control custom-input" id="password"
                           placeholder="<?= $isAdmin ? 'Admin Password' : 'Student Password' ?>" required>

                    <button type="button" class="toggle-password" id="togglePassword" tabindex="-1">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg id="eyeOffIcon" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>

                    <div class="text-end mt-1">
                        <a href="<?= $isAdmin ? 'forgot_password_admin.php' : 'forgot_password_student.php' ?>" class="forgot-password" id="forgotPasswordLink">Forgot password?</a>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-purple-login w-100">Log In</button>
                
            </form>

            <!-- Terms + Create account: Student only -->
            <div id="studentOnlyFooter" style="display: <?= $isAdmin ? 'none' : '' ?>;">
                <p class="terms-text mt-4">
                    By registering this, you agree to our <a href="terms_of_conditions.html">Terms and Conditions</a>
                </p>
                
                <p class="new-user-text">
                    New user? <a href="register.php">Create an account</a>
                </p>
            </div>

        </div>
    </div>

    <script>
        const btnAdmin = document.getElementById('btnAdmin');
        const btnStudent = document.getElementById('btnStudent');
        const roleInput = document.getElementById('roleInput');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const googleSection = document.getElementById('googleSection');
        const studentOnlyFooter = document.getElementById('studentOnlyFooter');
        const forgotPasswordLink = document.getElementById('forgotPasswordLink');

        btnAdmin.addEventListener('click', () => {
            btnAdmin.classList.add('active');
            btnStudent.classList.remove('active');
            roleInput.value = 'admin'; 
            emailInput.placeholder = 'Admin Email';
            passwordInput.placeholder = 'Admin Password';

            googleSection.style.display = 'none';
            studentOnlyFooter.style.display = 'none';
            forgotPasswordLink.href = 'forgot_password_admin.php';
        });

        btnStudent.addEventListener('click', () => {
            btnStudent.classList.add('active');
            btnAdmin.classList.remove('active');
            roleInput.value = 'student'; 
            emailInput.placeholder = 'Student Email';
            passwordInput.placeholder = 'Student Password';

            googleSection.style.display = '';
            studentOnlyFooter.style.display = '';
            forgotPasswordLink.href = 'forgot_password_student.php';
        });

        // Show/hide password toggle
        const togglePassword = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeOffIcon = document.getElementById('eyeOffIcon');

        togglePassword.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            eyeIcon.style.display = isHidden ? 'none' : '';
            eyeOffIcon.style.display = isHidden ? '' : 'none';
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const firebaseConfig = {
                apiKey: "AIzaSyAk11evif-LvDAnj_DHgrjGMVwbe-2S4QI",
                authDomain: "uitmnotelink.firebaseapp.com",
                projectId: "uitmnotelink",
                storageBucket: "uitmnotelink.firebasestorage.app",
                messagingSenderId: "445462119384",
                appId: "1:445462119384:web:01cbc4795b381f474728ad",
            };

            firebase.initializeApp(firebaseConfig);

            let isSigningIn = false;

            document.getElementById('googleSignInBtn').addEventListener('click', function () {
                if (isSigningIn) return;
                isSigningIn = true;

                const provider = new firebase.auth.GoogleAuthProvider();

                firebase.auth().signInWithPopup(provider)
                    .then((result) => {
                        const user = result.user;
                        return fetch('google_login.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                email: user.email,
                                name: user.displayName,
                                uid: user.uid
                            })
                        });
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.message || 'Google sign-in failed. Please try again.');
                        }
                    })
                    .catch((error) => {
                        const harmless = ['auth/cancelled-popup-request', 'auth/popup-closed-by-user'];
                        if (!harmless.includes(error.code)) {
                            console.error(error);
                            alert('Google sign-in failed: ' + error.message);
                        }
                    })
                    .finally(() => {
                        isSigningIn = false;
                    });
            });

        });
    </script>

</body>
</html>