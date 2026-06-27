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
                New user? <a href="register.html">Create an account</a>
            </p>

        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 UiTMNoteLink. All rights reserved.</p>
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
</body>
</html>