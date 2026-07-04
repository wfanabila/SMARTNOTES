<?php
session_start();
$error_msg = "";

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
                $_SESSION['user_name'] = $user['studentName'];
                $_SESSION['role'] = 'student';
                
                header("Location: user_dashboard.html");
                exit();
            } else {
                $error_msg = "Incorrect password!";
            }
        } else {
            $error_msg = "Student account not found!";
        }
    } 
    else if ($role === 'admin') {
        $error_msg = "Admin system is not ready yet. Please sign in as a Student.";
    }
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
                <button type="button" class="role-btn" id="btnAdmin">Admin</button>
                <button type="button" class="role-btn active" id="btnStudent">Student</button>
            </div>

            <button type="button" id="googleSignInBtn" class="google-btn">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="18" height="18" alt="Google">
                Continue with Google
            </button>

            <div class="divider"><span>or</span></div>

            <form action="login.php" method="post">
                
                <input type="hidden" name="role" id="roleInput" value="student">
                
                <div class="mb-3 text-start">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control custom-input" id="email" placeholder="Student Email" required>
                </div>
                
                <div class="mb-4 text-start position-relative">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control custom-input" id="password" placeholder="Student Password" required>
                    <div class="text-end mt-1">
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-purple-login w-100">Log In</button>
                
            </form>

            <p class="terms-text mt-4">
                By registering this, you agree to our <a href="terms_of_conditions.html">Terms and Conditions</a>
            </p>
            
            <p class="new-user-text">
                New user? <a href="register.php">Create an account</a>
            </p>

        </div>
    </div>

    <script>
        const btnAdmin = document.getElementById('btnAdmin');
        const btnStudent = document.getElementById('btnStudent');
        const roleInput = document.getElementById('roleInput');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        btnAdmin.addEventListener('click', () => {
            btnAdmin.classList.add('active');
            btnStudent.classList.remove('active');
            roleInput.value = 'admin'; 
            emailInput.placeholder = 'Admin Email';
            passwordInput.placeholder = 'Admin Password';
        });

        btnStudent.addEventListener('click', () => {
            btnStudent.classList.add('active');
            btnAdmin.classList.remove('active');
            roleInput.value = 'student'; 
            emailInput.placeholder = 'Student Email';
            passwordInput.placeholder = 'Student Password';
        });
    </script>

    <script>
        // Firebase Google Sign-In — dibungkus dalam DOMContentLoaded supaya
        // button dah wujud dalam page dulu sebelum kita "wire" click event
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

            let isSigningIn = false; // guard against double-click / duplicate popup requests

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
                        // Benign errors from double-clicks / closing the popup - stay silent
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