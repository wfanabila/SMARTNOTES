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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                        <div class="profile-initials"><?= htmlspecialchars($adminInitial) ?></div>
                        <div>
                            <h2><?= htmlspecialchars($adminName) ?></h2>
                            <p class="email">( <?= htmlspecialchars($adminRole) ?> )</p>
                        </div>
                    </div>
                    <div class="settings-card__actions">
                        <button type="button" class="btn btn--secondary">Change Profile Picture</button>
                        <button type="button" class="btn btn--outline">Change Password</button>
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
    </script>
</body>
</html>
