<?php
session_start();

$host = "localhost";
$username = "root"; 
$password = "";     
$dbname = "smartnotes";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Run database migrations to ensure all required columns exist
require_once 'migration_helper.php';
runAllMigrations($pdo);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $student_name = trim($_POST['student_name']);
    $email        = trim($_POST['email']);
    $programme    = trim($_POST['programme']);
    $semester     = trim($_POST['semester']);
    $bio          = trim($_POST['bio']);

    if (empty($student_name) || empty($email)) {
        $error_message = "Full Name and Email fields are required.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE student SET studentName = ?, studentEmail = ?, programme = ?, semester = ?, bio = ? WHERE studentID = ?");
            $stmt->execute([$student_name, $email, $programme, $semester, $bio, $user_id]);
            $message = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error_message = "Error updating profile: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $error_message = "All password fields are required.";
    } elseif (strlen($new_pass) < 8) {
        $error_message = "New password must be at least 8 characters.";
    } elseif ($new_pass !== $confirm_pass) {
        $error_message = "New password and verification do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT studentPassword FROM student WHERE studentID = ?");
        $stmt->execute([$user_id]);
        $user_pwd = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_pwd && $current_pass === $user_pwd['studentPassword']) {
            if ($current_pass === $new_pass) {
                $error_message = "New password must be different from your current one.";
            } else {
                $update_stmt = $pdo->prepare("UPDATE student SET studentPassword = ? WHERE studentID = ?");
                $update_stmt->execute([$new_pass, $user_id]);
                $message = "Password updated successfully!";
            }
        } else {
            $error_message = "Incorrect current password.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_picture') {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {

        $allowedTypes = ['image/jpeg', 'image/png'];
        $fileType = $_FILES['profile_picture']['type'];
        $fileTmp  = $_FILES['profile_picture']['tmp_name'];
        $fileSize = $_FILES['profile_picture']['size'];
        $maxSize  = 5 * 1024 * 1024; // 5MB

        if (!in_array($fileType, $allowedTypes)) {
            $error_message = "Only JPG and PNG images are allowed.";
        } elseif ($fileSize > $maxSize) {
            $error_message = "Image must be smaller than 5MB.";
        } else {
            $ext = ($fileType === 'image/png') ? 'png' : 'jpg';
            $uploadDir = __DIR__ . '/uploads/profile_pics/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newFileName = 'pfp_' . $user_id . '_' . uniqid() . '.' . $ext;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $destination)) {
                // Delete old profile picture if exists
                $stmtOld = $pdo->prepare("SELECT profilePicture FROM student WHERE studentID = ?");
                $stmtOld->execute([$user_id]);
                $oldPic = $stmtOld->fetchColumn();
                if ($oldPic && file_exists(__DIR__ . '/' . $oldPic)) {
                    @unlink(__DIR__ . '/' . $oldPic);
                }

                // Save new profile picture path to database
                $relativePath = 'uploads/profile_pics/' . $newFileName;
                $updateStmt = $pdo->prepare("UPDATE student SET profilePicture = ? WHERE studentID = ?");
                $updateStmt->execute([$relativePath, $user_id]);
                $message = "Profile picture uploaded successfully!";
            } else {
                $error_message = "Failed to upload the image. Check folder permissions.";
            }
        }
    } else {
        $error_message = "Please choose a PNG or JPG image to upload.";
    }
}

$stmt = $pdo->prepare("SELECT studentName, studentEmail, programme, semester, profilePicture, bio FROM student WHERE studentID = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User data could not be retrieved.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Account Setting</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/account_setting.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    
    <style>
        .alert-banner { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-size: 14px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .settings-form__row--full {
            display: flex;
            justify-content: center;
            width: 100%;
            margin-bottom: 25px;
        }
        
        .settings-form__row--full .settings-form__field {
            width: 100%;
            max-width: 900px; 
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .settings-form__row--full label {
            text-align: left;
            width: 100%;
            margin-bottom: 6px;
            font-weight: 500;
        }
        
        .settings-form__row--full .settings-form__input-wrap {
            width: 100%;
            position: relative;
        }

        .settings-form__row--full textarea {
            width: 100%;
            box-sizing: border-box;
        }
        
        .settings-form__row--full .settings-form__edit-icon--textarea {
            right: 12px;
            top: 12px;
        }

        .settings-form__actions-center {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <nav class="topnav">
        <div class="topnav__left">
            <a class="topnav__logo" href="#">
                <span class="topnav__logo-icon">
                    <img src="img/logo.PNG" alt="UiTMNoteLink Logo">
                </span>
            </a>
        </div>

        <div class="topnav__links">
            <a href="landingpage.php" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Home</a>
            <a href="#" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Notes</a>
            <a href="#" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link">Contributors</a>
            <a href="user_dashboard.php" class="w3-bar-item w3-button w3-hover-none w3-border-white w3-bottombar topnav-link topnav-link--active">Dashboard</a>
        </div>

        <div class="topnav__right">
            <div class="topnav__avatar">
                <?php if (!empty($user['profilePicture'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profilePicture']); ?>" alt="Profile picture" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php include 'sidebar.php'; ?>

        <main class="main">
            <div class="main__container">

                <header class="settings-header">
                    <h1 class="settings-header__title">Account Setting</h1>
                    <p class="settings-header__sub">Update your personal information and payment methods for student rewards.</p>
                </header>

                <?php if (!empty($message)): ?>
                    <div class="alert-banner alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if (!empty($error_message)): ?>
                    <div class="alert-banner alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <section class="settings-card">
                    <div class="settings-card__top">
                        <div class="settings-card__identity">
                            <div class="settings-card__avatar">
                                <?php if (!empty($user['profilePicture'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['profilePicture']); ?>" alt="Profile picture" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="8" r="4"/>
                                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="settings-card__name"><?php echo htmlspecialchars($user['studentName']); ?></p>
                                <p class="settings-card__email"><?php echo htmlspecialchars($user['studentEmail']); ?></p>
                            </div>
                        </div>

                        <div class="settings-card__actions">
                            <button type="button" class="btn btn--primary" onclick="openPfpModal()">Change Profile Picture</button>
                            <div class="modal-overlay" id="pfp-modal-overlay" onclick="closePfpModalOutside(event)">
                                <div class="pfp-modal">
                                    <form id="pfp-form" action="account_setting.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="update_picture">
                                        <input type="file" name="profile_picture" id="pfp-file-input" accept="image/png, image/jpeg" style="display: none;">
                                        <div class="pfp-dropzone" id="pfp-dropzone">
                                            <div class="pfp-dropzone__content">
                                                <div class="pfp-dropzone__icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M12 13v8M12 13l4 4M12 13l-4 4"/>
                                                        <path d="M20.88 18.04A6 6 0 0 0 6 11a5 5 0 0 0-3.5 8.5"/>
                                                    </svg>
                                                </div>
                                                <h3>Upload your Photo here</h3>
                                                <p>PNG or JPG files accepted. High resolution recommended.</p>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <button type="button" class="btn btn--outline" id="change-password-btn" onclick="openPasswordModal()">Change Password</button>
                        </div>
                    </div>

                    <form class="settings-form" action="account_setting.php" method="POST">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="settings-form__row">
                            <div class="settings-form__field">
                                <label for="student-name">Full Name</label>
                                <div class="settings-form__input-wrap">
                                    <input type="text" id="student-name" name="student_name" value="<?php echo htmlspecialchars($user['studentName']); ?>" required>
                                    <svg class="settings-form__edit-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="settings-form__field">
                                <label for="email">Email Address</label>
                                <div class="settings-form__input-wrap">
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['studentEmail']); ?>" required>
                                    <svg class="settings-form__edit-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="settings-form__row">
                            <div class="settings-form__field">
                                <label for="programme">Programme</label>
                                <div class="settings-form__input-wrap">
                                    <input type="text" id="programme" name="programme" value="<?php echo htmlspecialchars($user['programme']); ?>">
                                    <svg class="settings-form__edit-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="settings-form__field">
                                <label for="semester">Semester</label>
                                <div class="settings-form__input-wrap">
                                    <input type="number" id="semester" name="semester" min="1" max="8" value="<?php echo htmlspecialchars($user['semester']); ?>">
                                    <svg class="settings-form__edit-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="settings-form__row settings-form__row--full">
                            <div class="settings-form__field">
                                <label for="bio">Bio</label>
                                <div class="settings-form__input-wrap">
                                    <textarea id="bio" name="bio" rows="4" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                    <svg class="settings-form__edit-icon settings-form__edit-icon--textarea" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="settings-form__actions-center">
                            <button type="submit" class="btn btn--primary btn--save">Save Changes</button>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="password-modal-overlay">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="password-modal-title">

            <div class="modal__header">
                <div class="modal__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="11" width="14" height="9" rx="2"/>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="modal__title" id="password-modal-title">Change Password</h2>
                    <p class="modal__subtitle">Update your account password</p>
                </div>
            </div>

            <form class="modal__form" id="password-form" action="account_setting.php" method="POST" onsubmit="return validatePasswordForm();">
                <input type="hidden" name="action" value="update_password">

                <div class="modal__field">
                    <label for="current-password">Current Password</label>
                    <div class="modal__input-wrap">
                        <input type="password" id="current-password" name="current_password" placeholder="Enter Current Password" autocomplete="current-password">
                        <button type="button" class="modal__eye-btn" data-target="current-password" onclick="togglePasswordVisibility(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="modal__field">
                    <label for="new-password">New Password</label>
                    <div class="modal__input-wrap">
                        <input type="password" id="new-password" name="new_password" placeholder="Enter New Password" autocomplete="new-password">
                        <button type="button" class="modal__eye-btn" data-target="new-password" onclick="togglePasswordVisibility(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="modal__field">
                    <label for="confirm-password">Confirm New Password</label>
                    <div class="modal__input-wrap">
                        <input type="password" id="confirm-password" name="confirm_password" placeholder="Re-Enter New Password" autocomplete="new-password">
                        <button type="button" class="modal__eye-btn" data-target="confirm-password" onclick="togglePasswordVisibility(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <p class="modal__error" id="password-error"></p>

                <div class="modal__actions">
                    <button type="button" class="btn btn--outline" onclick="closePasswordModal()">Cancel</button>
                    <button type="submit" class="btn btn--primary" id="update-password-btn">Update Password</button>
                </div>

            </form>
        </div>
    </div>

    <script>
    const settingsBtn = document.getElementById('nav-settings');
    const accountPopup = document.getElementById('account-popup');

    function toggleSettingsPopup(event) {
        event.preventDefault();
        event.stopPropagation();
        accountPopup.classList.toggle('show');
    }

    window.addEventListener('click', function (event) {
        if (accountPopup && accountPopup.classList.contains('show') &&
            !accountPopup.contains(event.target) &&
            event.target !== settingsBtn) {
            accountPopup.classList.remove('show');
        }
    });

    const passwordModalOverlay = document.getElementById('password-modal-overlay');
    const passwordForm = document.getElementById('password-form');
    const passwordError = document.getElementById('password-error');
    const currentPasswordInput = document.getElementById('current-password');
    const newPasswordInput = document.getElementById('new-password');
    const confirmPasswordInput = document.getElementById('confirm-password');

    function openPasswordModal() {
        passwordModalOverlay.classList.add('show');
    }

    function closePasswordModal() {
        passwordModalOverlay.classList.remove('show');
        passwordForm.reset();
        passwordError.textContent = '';
        passwordError.classList.remove('show');

        [currentPasswordInput, newPasswordInput, confirmPasswordInput].forEach(function (input) {
            input.type = 'password';
        });
        document.querySelectorAll('.modal__eye-btn').forEach(function (btn) {
            btn.classList.remove('is-visible');
        });
    }

    function togglePasswordVisibility(button) {
        const targetId = button.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        button.classList.toggle('is-visible', isPassword);
    }

    function showPasswordError(message) {
        passwordError.textContent = message;
        passwordError.classList.add('show');
    }

    function validatePasswordForm() {
        const currentPassword = currentPasswordInput.value;
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        passwordError.textContent = '';
        passwordError.classList.remove('show');

        if (!currentPassword || !newPassword || !confirmPassword) {
            showPasswordError('Please fill in all fields.');
            return false;
        }

        if (newPassword.length < 8) {
            showPasswordError('New password must be at least 8 characters.');
            return false;
        }

        if (newPassword !== confirmPassword) {
            showPasswordError('New password and confirmation do not match.');
            return false;
        }

        if (newPassword === currentPassword) {
            showPasswordError('New password must be different from the current password.');
            return false;
        }

        return true;
    }

    const pfpModalOverlay = document.getElementById('pfp-modal-overlay');
    const pfpFileInput = document.getElementById('pfp-file-input');
    const pfpDropzone = document.getElementById('pfp-dropzone');
    const pfpForm = document.getElementById('pfp-form');
    const maxPfpSize = 5 * 1024 * 1024; // 5MB

    function openPfpModal() {
        pfpModalOverlay.classList.add('show');
    }

    function closePfpModal() {
        pfpModalOverlay.classList.remove('show');
        pfpDropzone.classList.remove('dragover');
    }

    function closePfpModalOutside(event) {
        if (event.target === pfpModalOverlay) {
            closePfpModal();
        }
    }

    pfpDropzone.addEventListener('click', () => {
        pfpFileInput.click();
    });

    pfpFileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            handleImageUpload(this.files[0]);
        }
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        pfpDropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            pfpDropzone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        pfpDropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            pfpDropzone.classList.remove('dragover');
        }, false);
    });

    pfpDropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files[0]) {
            pfpFileInput.files = files;
            handleImageUpload(files[0]);
        }
    });

    function handleImageUpload(file) {
        if (file.type !== "image/jpeg" && file.type !== "image/png") {
            alert("Please upload only PNG or JPG files.");
            return;
        }

        if (file.size > maxPfpSize) {
            alert("Image must be smaller than 5MB.");
            return;
        }

        pfpDropzone.classList.add('uploading');
        pfpForm.submit();
    }
    </script>
</body>
</html>