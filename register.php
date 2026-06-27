<?php
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
        echo "<script>alert('Student ID or Email already exists!'); window.location.href='register.html';</script>";
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