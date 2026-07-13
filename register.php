<?php
$from = (isset($_GET['from']) && $_GET['from'] === 'login') ? 'login' : 'frontpage';
$backHref = ($from === 'login') ? 'login.php' : 'front_page.html';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $db_user = "root";
    $db_pass = ""; 
    $db_name = "smartnotes"; 

    $conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $id = mysqli_real_escape_string($conn, $_POST['studentID']);
    $name = mysqli_real_escape_string($conn, $_POST['studentName']);
    $email = mysqli_real_escape_string($conn, $_POST['studentEmail']);
    $programme = mysqli_real_escape_string($conn, $_POST['programme']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']); 
    $password = mysqli_real_escape_string($conn, $_POST['studentPassword']);

    $check_user = "SELECT * FROM student WHERE studentEmail='$email' OR studentID='$id' LIMIT 1";
    $result = mysqli_query($conn, $check_user);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Student ID or Email already exists!'); window.location.href='register.php?from=$from';</script>";
    } else {
        $query = "INSERT INTO student (studentID, studentName, studentEmail, studentPassword, programme, semester) 
                  VALUES ('$id', '$name', '$email', '$password', '$programme', '$semester')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Registration successful! Please log in.'); window.location.href='login.php';</script>";
        } else {
            echo "Error: " . $query . "<br>" . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTMNoteLink - Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">  
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-login.css">
    <style>
        .terms-link:hover {
            text-decoration: underline !important;
        }
    </style>
</head>
<body>
    
    <a href="<?= $backHref ?>" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16" style="stroke: currentColor; stroke-width: 1px;">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
        </svg>
        Back
    </a>

    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="text-center mb-3">
                <img class="login-logo" src="https://u.pone.rs/nylrmwij.png" alt="UiTMNoteLink Logo">
            </div>

            <h1 class="login-title" style="font-size: 1.85rem;">Registration</h1>
            <p class="login-subtitle" style="margin-bottom: 15px;">Register your account to join our community</p>

            <form action="register.php?from=<?= $from ?>" method="post">
                
                <div class="mb-2 text-start">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="studentName" class="form-control custom-input" id="name" placeholder="e.g. Ahmad Dhani" required>
                </div>

                <div class="mb-2 text-start">
                    <label for="studentID" class="form-label">Student ID</label>
                    <input type="text" name="studentID" class="form-control custom-input" id="studentID" placeholder="e.g. 2023492098" required>
                </div>

                <div class="mb-2 text-start">
                    <label for="programme" class="form-label">Programme</label>
                    <input type="text" name="programme" class="form-control custom-input" id="programme" placeholder="e.g. CS255" required>
                </div>

                <div class="mb-2 text-start">
                    <label for="semester" class="form-label">Current Semester</label>
                   <select name="semester" class="form-select custom-input" id="semester" required>
    <option value="" disabled selected>Select your semester</option> <option value="1">1</option>
    <option value="2">2</option>
    <option value="3">3</option>
    <option value="4">4</option>
    <option value="5">5</option>
    <option value="6">6</option>
</select>
                </div>

                <div class="mb-2 text-start">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="studentEmail" class="form-control custom-input" id="email" placeholder="you@student.uitm.edu.my" required>
                </div>

                <div class="mb-3 text-start">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="studentPassword" class="form-control custom-input" id="password" placeholder="Create a password" required>
                </div>

                <p class="terms-text" style="font-size: 0.72rem; margin-top: 15px; margin-bottom: 15px; color: #777777;">
                    By registering this, you agree to our <a href="terms_of_conditions.html" class="terms-link" style="color: #333333; font-weight: 600; text-decoration: none;">Terms and Conditions</a>
                </p>
                
                <button type="submit" class="btn btn-purple-login w-100" style="border-radius: 20px;">Create account</button>
                
            </form>

        </div>
    </div>
</body>
</html>