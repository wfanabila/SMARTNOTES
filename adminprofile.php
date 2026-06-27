<?php
$adminName = 'Putri Amira binti Abdullah';
$adminEmail = 'putri@admin.noteshare.my';
$adminPhone = '+60 12 345 6789';
$adminRole = 'Super Admin';
$adminDept = 'Computer Science';
$adminSince = 'January 2025';
$adminInitial = strtoupper(substr($adminName, 0, 1));

$fullName = $adminName;
$email = $adminEmail;
$phone = $adminPhone;
$role = $adminRole;
$theme = 'light';
$errorMessage = '';
$successMessage = '';
$uploadErrorMessage = '';
$uploadSuccessMessage = '';
$passwordErrorMessage = '';
$passwordSuccessMessage = '';
$adminPassword = 'Password123';

$uploadDir = 'uploads/';
$uploadFileName = 'admin_profile.png';
$uploadedFilePath = $uploadDir . $uploadFileName;
$profilePictureUrl = file_exists($uploadedFilePath) ? $uploadedFilePath : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'uploadProfilePicture') {
        if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg' => '.jpg', 'image/png' => '.png'];
            $fileType = mime_content_type($_FILES['profileImage']['tmp_name']);

            if (!array_key_exists($fileType, $allowedTypes)) {
                $uploadErrorMessage = 'Please upload a JPG or PNG image.';
            } else {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $targetPath = $uploadDir . $uploadFileName;
                if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetPath)) {
                    $uploadSuccessMessage = 'Profile picture uploaded successfully.';
                    $profilePictureUrl = $targetPath;
                } else {
                    $uploadErrorMessage = 'Unable to upload image. Try again.';
                }
            }
        } else {
            $uploadErrorMessage = 'Choose a picture first to upload.';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'changePassword') {
        $oldPassword = trim($_POST['oldPassword'] ?? '');
        $newPassword = trim($_POST['newPassword'] ?? '');
        $confirmPassword = trim($_POST['confirmPassword'] ?? '');

        if ($oldPassword !== $adminPassword) {
            $passwordErrorMessage = 'Incorrect current password.';
        } elseif ($newPassword === '') {
            $passwordErrorMessage = 'New password cannot be empty.';
        } elseif ($newPassword !== $confirmPassword) {
            $passwordErrorMessage = 'New passwords do not match.';
        } else {
            $passwordSuccessMessage = 'Password changed successfully.';
        }
    } else {
        $fullName = trim($_POST['fullName'] ?? $adminName);
        $email = trim($_POST['email'] ?? $adminEmail);
        $phone = trim($_POST['phone'] ?? $adminPhone);
        $role = trim($_POST['role'] ?? $adminRole);
        $theme = $_POST['theme'] ?? 'light';

        $isEmailValid = filter_var($email, FILTER_VALIDATE_EMAIL);
        $isPhoneValid = preg_match('/^\+60\s?\d{2}\s?\d{3}\s?\d{4}$/', $phone);

        if (!$isEmailValid || $email !== $adminEmail || !$isPhoneValid) {
            $errorMessage = '✕ Phone Number doesn\'t exist. Please insert the correct Email.';
        } else {
            $successMessage = 'Profile updated successfully.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTMNoteLink - Admin Profile</title>
    <link rel="stylesheet" href="css/adminprofile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <img src="img/logo.PNG" alt="UiTMNoteLink Logo" class="brand-logo-img">
            </div>
            <nav class="menu">
                <a href="admin.html" class="menu-item">
                    <span class="menu-icon"><i class="fas fa-th-large"></i></span>
                    <span>Dashboard</span>
                </a>
                <a href="manage_students.html" class="menu-item">
                    <span class="menu-icon"><i class="fas fa-users"></i></span>
                    <span>Manage Students</span>
                </a>
                <a href="manage_notes.html" class="menu-item">
                    <span class="menu-icon"><i class="far fa-file-alt"></i></span>
                    <span>Manage Notes</span>
                </a>
                <a href="adminprofile.php" class="menu-item active">
                    <span class="menu-icon"><i class="fas fa-id-card"></i></span>
                    <span>Admin’s Profile</span>
                </a>
                <div class="menu-divider"></div>
                <div class="sidebar-card">
                    <div class="sidebar-card__top">
                        <div class="sidebar-card__avatar"><?= htmlspecialchars($adminInitial) ?></div>
                        <div>
                            <p class="sidebar-card__name"><?= htmlspecialchars($adminName) ?></p>
                            <p class="sidebar-card__email"><?= htmlspecialchars($adminEmail) ?></p>
                        </div>
                    </div>
                    <div class="sidebar-card__links">
                        <a href="adminprofile.php" class="sidebar-card__link active">Account Setting</a>
                        <a href="login.php" class="sidebar-card__link logout">Log Out</a>
                    </div>
                </div>
                <a href="help_center.html" class="menu-item">
                    <span class="menu-icon"><i class="far fa-question-circle"></i></span>
                    <span>Help Center</span>
                </a>
                <a href="login.php" class="menu-item sign-out">
                    <span class="menu-icon"><i class="fas fa-sign-out-alt"></i></span>
                    <span>Sign Out</span>
                </a>
            </nav>
        </aside>

        <main class="content">
            <header class="topbar">
                <div class="top-nav">
                    <a href="help_center.html">Contributors</a>
                    <a href="admin.html" class="active">Dashboard</a>
                </div>
                <div class="profile-area" id="profileToggle">
                    <div class="profile-circle"><?= htmlspecialchars($adminInitial) ?></div>
                    <i class="fas fa-chevron-down"></i>

                    <div class="account-popup" id="accountPopup">
                        <p class="account-popup__label">Account</p>
                        <div class="account-popup__user">
                            <div class="account-popup__avatar"><?= htmlspecialchars($adminInitial) ?></div>
                            <div>
                                <p class="account-popup__name"><?= htmlspecialchars($adminName) ?></p>
                                <p class="account-popup__email"><?= htmlspecialchars($adminEmail) ?></p>
                            </div>
                        </div>
                        <div class="account-popup__divider"></div>
                        <a href="adminprofile.php" class="account-popup__item account-popup__item--active">
                            <span>Account Setting</span>
                        </a>
                        <a href="login.php" class="account-popup__item account-popup__item--logout">
                            <span>Log Out</span>
                        </a>
                    </div>
                </div>
            </header>

            <div class="page-head">
                <div>
                    <h1>Account Setting</h1>
                    <p class="page-subtitle">Update your personal information and payment methods for student rewards.</p>
                </div>
            </div>

            <section class="profile-panel">
                <div class="profile-card settings-card">
                    <div class="profile-card__avatar">
                        <?php if ($profilePictureUrl): ?>
                            <div class="profile-picture" style="background-image: url('<?= htmlspecialchars($profilePictureUrl) ?>');"></div>
                        <?php else: ?>
                            <div class="profile-initials"><?= htmlspecialchars($adminInitial) ?></div>
                        <?php endif; ?>
                        <div>
                            <h2><?= htmlspecialchars($adminName) ?></h2>
                            <p class="email">( <?= htmlspecialchars($adminRole) ?> )</p>
                        </div>
                    </div>
                    <div class="settings-card__actions">
                        <button type="button" class="btn btn--secondary" id="uploadOpenButton">Change Profile Picture</button>
                        <button type="button" class="btn btn--outline" id="passwordOpenButton">Change Password</button>
                    </div>
                </div>

                <form class="settings-form" method="post" novalidate>
                    <div class="settings-form__row">
                        <div class="settings-form__field">
                            <label for="fullName">Full Name</label>
                            <div class="input-with-icon">
                                <input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($fullName) ?>" required>
                                <span class="edit-icon"><i class="fas fa-pencil-alt"></i></span>
                            </div>
                        </div>
                        <div class="settings-form__field">
                            <label for="email">Email Address</label>
                            <div class="input-with-icon">
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                                <span class="edit-icon"><i class="fas fa-pencil-alt"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="settings-form__row">
                        <div class="settings-form__field">
                            <label for="phone">Phone Number</label>
                            <div class="input-with-icon">
                                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
                                <span class="edit-icon"><i class="fas fa-pencil-alt"></i></span>
                            </div>
                            <?php if ($errorMessage): ?>
                                <p class="form-error"><?= htmlspecialchars($errorMessage) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="settings-form__field">
                            <label for="role">Role</label>
                            <div class="input-with-icon">
                                <input type="text" id="role" name="role" value="<?= htmlspecialchars($role) ?>" required>
                                <span class="edit-icon"><i class="fas fa-pencil-alt"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="settings-form__submit">
                        <button type="submit" class="btn btn--primary">Save Changes</button>
                        <?php if ($successMessage): ?>
                            <p class="form-success"><?= htmlspecialchars($successMessage) ?></p>
                        <?php endif; ?>
                    </div>
                </form>

                </form>

                <div class="upload-modal-overlay" id="uploadModalOverlay">
                    <div class="upload-modal">
                        <div class="upload-modal__header">
                            <h3>Upload your Photo here</h3>
                            <button type="button" class="upload-modal__close" id="uploadCloseButton">×</button>
                        </div>
                        <form class="upload-form" action="adminprofile.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="uploadProfilePicture">
                            <div class="upload-dropzone" id="uploadDropzone">
                                <span class="upload-dropzone__icon"><i class="fas fa-cloud-arrow-up"></i></span>
                                <h4>Upload your Photo here</h4>
                                <p>PNG or JPG files accepted. High resolution recommended.</p>
                                <input type="file" name="profileImage" id="profileImageInput" accept="image/png, image/jpeg">
                                <button type="button" class="btn btn--outline upload-browse" id="browseButton">Browse Picture</button>
                                <p class="upload-note">Max size 5MB.</p>
                            </div>
                            <?php if ($uploadErrorMessage): ?>
                                <p class="form-error upload-error"><?= htmlspecialchars($uploadErrorMessage) ?></p>
                            <?php endif; ?>
                            <?php if ($uploadSuccessMessage): ?>
                                <p class="form-success upload-success"><?= htmlspecialchars($uploadSuccessMessage) ?></p>
                            <?php endif; ?>
                            <div class="upload-form__actions">
                                <button type="submit" class="btn btn--primary">Upload</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="upload-modal-overlay" id="passwordModalOverlay">
                    <div class="upload-modal">
                        <div class="upload-modal__header">
                            <h3>Change Password</h3>
                            <button type="button" class="upload-modal__close" id="passwordCloseButton">×</button>
                        </div>
                        <form class="upload-form" action="adminprofile.php" method="post">
                            <input type="hidden" name="action" value="changePassword">
                            <div class="settings-form__field">
                                <label for="oldPassword">Current Password</label>
                                <input type="password" id="oldPassword" name="oldPassword" required>
                            </div>
                            <div class="settings-form__field">
                                <label for="newPassword">New Password</label>
                                <input type="password" id="newPassword" name="newPassword" required>
                            </div>
                            <div class="settings-form__field">
                                <label for="confirmPassword">Confirm New Password</label>
                                <input type="password" id="confirmPassword" name="confirmPassword" required>
                            </div>
                            <?php if ($passwordErrorMessage): ?>
                                <p class="form-error upload-error"><?= htmlspecialchars($passwordErrorMessage) ?></p>
                            <?php endif; ?>
                            <?php if ($passwordSuccessMessage): ?>
                                <p class="form-success upload-success"><?= htmlspecialchars($passwordSuccessMessage) ?></p>
                            <?php endif; ?>
                            <div class="upload-form__actions">
                                <button type="submit" class="btn btn--primary">Save Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <section class="appearance-section">
                    <h2>Appearance</h2>
                    <p class="appearance-subtitle">Personalize your workspace experience.</p>
                    <div class="appearance-grid">
                            <label class="appearance-card<?= $theme === 'light' ? ' selected' : '' ?>">
                                <input type="radio" name="theme" value="light"<?= $theme === 'light' ? ' checked' : '' ?>>
                                <img src="img/lightmode.png" alt="Light Mode" class="appearance-image">
                                <span class="appearance-title">Light Mode</span>
                            </label>
                            <label class="appearance-card<?= $theme === 'dark' ? ' selected' : '' ?>">
                                <input type="radio" name="theme" value="dark"<?= $theme === 'dark' ? ' checked' : '' ?>>
                                <img src="img/darkmode.png" alt="Dark Mode" class="appearance-image">
                                <span class="appearance-title">Dark Mode</span>
                            </label>
                            <label class="appearance-card<?= $theme === 'system' ? ' selected' : '' ?>">
                                <input type="radio" name="theme" value="system"<?= $theme === 'system' ? ' checked' : '' ?>>
                                <img src="img/system.png" alt="System" class="appearance-image">
                                <span class="appearance-title">System</span>
                            </label>
                        </div>
                    </section>
                </form>
            </section>
        </main>
    </div>
    <script>
        document.addEventListener('click', function(event) {
            var popup = document.getElementById('accountPopup');
            var toggle = document.getElementById('profileToggle');
            if (!toggle.contains(event.target)) {
                popup.classList.remove('visible');
            }
        });
        document.getElementById('profileToggle').addEventListener('click', function(event) {
            event.stopPropagation();
            document.getElementById('accountPopup').classList.toggle('visible');
        });

        var uploadOpenButton = document.getElementById('uploadOpenButton');
        var uploadCloseButton = document.getElementById('uploadCloseButton');
        var uploadModalOverlay = document.getElementById('uploadModalOverlay');
        var uploadDropzone = document.getElementById('uploadDropzone');
        var fileInput = document.getElementById('profileImageInput');
        var browseButton = document.getElementById('browseButton');
        var passwordOpenButton = document.getElementById('passwordOpenButton');
        var passwordCloseButton = document.getElementById('passwordCloseButton');
        var passwordModalOverlay = document.getElementById('passwordModalOverlay');

        function openUploadModal() {
            uploadModalOverlay.classList.add('visible');
        }

        function closeUploadModal() {
            uploadModalOverlay.classList.remove('visible');
        }

        function openPasswordModal() {
            passwordModalOverlay.classList.add('visible');
        }

        function closePasswordModal() {
            passwordModalOverlay.classList.remove('visible');
        }

        uploadOpenButton.addEventListener('click', openUploadModal);
        uploadCloseButton.addEventListener('click', closeUploadModal);
        uploadModalOverlay.addEventListener('click', function(event) {
            if (event.target === uploadModalOverlay) {
                closeUploadModal();
            }
        });
        browseButton.addEventListener('click', function() {
            fileInput.click();
        });
        uploadDropzone.addEventListener('click', function() {
            fileInput.click();
        });

        passwordOpenButton.addEventListener('click', openPasswordModal);
        passwordCloseButton.addEventListener('click', closePasswordModal);
        passwordModalOverlay.addEventListener('click', function(event) {
            if (event.target === passwordModalOverlay) {
                closePasswordModal();
            }
        });
    </script>
</body>
</html>
