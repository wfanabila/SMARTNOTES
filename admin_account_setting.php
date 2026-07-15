<?php
require_once __DIR__ . '/admin_bootstrap.php';
$message=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action=$_POST['action']??'';
    if ($action==='profile') {
        $name=trim($_POST['adminName']??''); $email=trim($_POST['adminEmail']??''); $phone=trim($_POST['adminPhone']??''); $role=trim($_POST['adminRole']??''); $dept=trim($_POST['adminDepartment']??'');
        if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL)) $error='Name and a valid email are required.';
        else { $stmt=$conn->prepare('UPDATE admin SET adminName=?,adminEmail=?,adminPhone=?,adminRole=?,adminDepartment=? WHERE adminID=?'); $stmt->bind_param('ssssss',$name,$email,$phone,$role,$dept,$adminID); if($stmt->execute()){$_SESSION['user_name']=$name;$message='Profile saved.';$adminName=$name;$adminEmail=$email;$adminInitial=strtoupper(substr($name,0,1));}else $error='Unable to save profile.'; $stmt->close(); }
    } elseif ($action==='password') {
        $current = $_POST['currentPassword'] ?? '';
        $new = $_POST['newPassword'] ?? '';
        $confirm = $_POST['confirmPassword'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            $error = 'Fill in all password fields.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif (!is_array($admin) || !isset($admin['password']) || $current !== $admin['password']) {
            $error = 'Current password is incorrect.';
        } else {
            $stmt = $conn->prepare('UPDATE admin SET password=? WHERE adminID=?');
            $stmt->bind_param('ss', $new, $adminID);
            $stmt->execute();
            $stmt->close();
            $message = 'Password changed successfully.';
        }
    } elseif ($action==='picture' && isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error']===UPLOAD_ERR_OK) {
        $type=mime_content_type($_FILES['profilePicture']['tmp_name']);$size=(int)$_FILES['profilePicture']['size'];
        if(!in_array($type,['image/jpeg','image/png'],true)||$size>5*1024*1024)$error='Use a JPG or PNG image below 5MB.';else{$dir=__DIR__.'/uploads/admin_profiles/';if(!is_dir($dir))mkdir($dir,0755,true);$ext=$type==='image/png'?'png':'jpg';$file='admin_'.$adminID.'_'.uniqid().'.'.$ext;if(move_uploaded_file($_FILES['profilePicture']['tmp_name'],$dir.$file)){$path='uploads/admin_profiles/'.$file;$stmt=$conn->prepare('UPDATE admin SET profilePicture=? WHERE adminID=?');$stmt->bind_param('ss',$path,$adminID);$stmt->execute();$stmt->close();$admin['profilePicture']=$path;$message='Profile picture updated.';}else $error='Unable to upload image.';}
    }
}
$conn->close();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Account Setting | UiTM NoteLink</title><link rel="stylesheet" href="css/admin_account_setting.css"></head><body><header><a href="admin_dashboard.php"><img src="img/logo.PNG" alt="UiTM NoteLink"></a><nav><a href="admin_contributors.php">Contributors</a><a href="admin_dashboard.php">Dashboard</a></nav><a class="avatar" href="adminprofile.php"><?= admin_escape($adminInitial) ?></a></header><main><h1>Account Setting</h1><p>Update your personal information and profile picture.</p><?php if($message):?><div class="ok"><?= admin_escape($message) ?></div><?php endif;?><?php if($error):?><div class="err"><?= admin_escape($error) ?></div><?php endif;?><section class="hero"><div class="picture"><?php if(!empty($admin['profilePicture'])):?><img src="<?= admin_escape($admin['profilePicture']) ?>" alt="Profile picture"><?php else:?><b><?= admin_escape($adminInitial) ?></b><?php endif;?></div><div><h2><?= admin_escape($adminName) ?></h2><small><?= admin_escape($adminEmail) ?></small></div><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="picture"><label class="button">Change Profile Picture<input type="file" name="profilePicture" accept="image/png,image/jpeg" onchange="this.form.submit()"></label></form></section><form method="post" class="settings"><input type="hidden" name="action" value="profile"><label>Full Name<input name="adminName" value="<?= admin_escape($adminName) ?>" required></label><label>Email Address<input type="email" name="adminEmail" value="<?= admin_escape($adminEmail) ?>" required></label><label>Phone Number<input name="adminPhone" value="<?= admin_escape($admin['adminPhone']??'') ?>"></label><label>Role<input name="adminRole" value="<?= admin_escape($admin['adminRole']??'') ?>"></label><label>Department<input name="adminDepartment" value="<?= admin_escape($admin['adminDepartment']??'') ?>"></label><button>Save Changes</button></form><form method="post" class="password"><input type="hidden" name="action" value="password"><h2>Change Password</h2><p>Your current password is never displayed for security.</p><input type="password" name="currentPassword" placeholder="Current password" required><input type="password" name="newPassword" placeholder="New password (minimum 8 characters)" required><input type="password" name="confirmPassword" placeholder="Confirm new password" required><button>Change Password</button></form></main></body></html>

