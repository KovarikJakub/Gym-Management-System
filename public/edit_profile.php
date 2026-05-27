<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php';
require_once __DIR__ . '/../src/Utils/upload.php';
require_once __DIR__ . '/../src/Utils/helpers.php';

requireLogin();

$user = getUserById($_SESSION['user_id']);
$uploadDir = __DIR__ . '/uploads/';
$error = null;
$success = null;

$uploadsBaseUrl = 'uploads/'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    try
    {
        if (!verifyCsrf($_POST['csrf_token'] ?? ''))
            throw new Exception("CSRF token validation failed. Please try again.");

        $realName = trim($_POST['real_name']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $deletePhoto = isset($_POST['delete_photo']);
        $profilePhoto = null;

        if (empty($realName) || strlen($realName) > 255)
            throw new Exception("Invalid full name.");
        
        if (empty($username) || strlen($username) > 255)
            throw new Exception("Invalid username.");

        // Handle File Upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE)
        {
            $uploadResult = validateAndSaveImage($_FILES['profile_photo'], $uploadDir); 
            if (isset($uploadResult['error']))
                throw new Exception($uploadResult['error']);
            $profilePhoto = $uploadResult['filename'];
        }

        $passwordHash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
        
        $photoPathToDelete = null;
        if ($deletePhoto || $profilePhoto)
        {
            if (!isNullOrEmptyString($user['profile_photo']))
                $photoPathToDelete = $uploadDir . $user['profile_photo'];
        }

        try
        {
            updateUser($_SESSION['user_id'], $realName, $username, $passwordHash, $profilePhoto, $deletePhoto);
        } 
        catch (PDOException $e)
        {
            if (strpos($e->getMessage(), 'users.username') !== false)
                throw new Exception("Username already exists.");
            throw new Exception("An error occurred. Please try again.");
        }

        if ($photoPathToDelete)
            removeFileIfExists($photoPathToDelete);

        // Update session in case username changed
        $_SESSION['username'] = $username;
        
        // Refresh user data
        $user = getUserById($_SESSION['user_id']);
        $success = "Profile updated successfully!";

    }
    catch (Exception $e)
    {
        $error = $e->getMessage();
        
        // Cleanup newly uploaded photo if update fails
        if (isset($profilePhoto) && $profilePhoto) 
            removeFileIfExists($uploadDir . $profilePhoto);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/edit_profile.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <title>Edit Profile - Fit-Pro</title>
</head>
<body>
    <?php 
        require_once __DIR__ . '/../src/templates/common.php';
        outputHeader(); 
    ?>
    <main class="center-content">
        <div class="auth-container boxes">
            <h1>Edit Profile</h1>
            
            <?php if ($error): ?>
                <p style="color: var(--danger-color); background: rgba(224, 122, 95, 0.1); padding: 10px; border-radius: 5px;">
                    <?= htmlspecialchars($error) ?>
                </p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p style="color: var(--success-color); background: rgba(109, 191, 103, 0.1); padding: 10px; border-radius: 5px;">
                    <?= htmlspecialchars($success) ?>
                </p>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <label for="real_name">Full Name</label>
                <input type="text" name="real_name" id="real_name" value="<?= htmlspecialchars($user['real_name']) ?>" required>

                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>

                <label for="password">New Password (leave blank to keep current)</label>
                <input type="password" name="password" id="password" placeholder="••••••••">

                <div class="form-group">
                    <label for="profile_photo">Profile Photo</label>
                    <?php if (!isNullOrEmptyString($user['profile_photo'])): ?>
                        <div class="current-photo-preview">
                            <img src="<?= $uploadsBaseUrl . htmlspecialchars($user['profile_photo']) ?>" alt="Profile Photo">
                            <div style="margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <input type="checkbox" name="delete_photo" id="delete_photo" style="width: auto; margin: 0;">
                                <label for="delete_photo" style="font-size: 0.9em; opacity: 0.8;">Remove current photo</label>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="current-photo-preview">
                            <img src="<?= $uploadsBaseUrl . "placeholder.jpg" ?>" alt="Profile Photo">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="profile_photo" id="profile_photo" accept="image/*">
                </div>

                <button type="submit" class="btn">Update Profile</button>
            </form>
            
            <p><a href="dashboard.php" style="font-size: 0.9em; opacity: 0.8;">← Back to Dashboard</a></p>
        </div>
    </main>
    <?php outputFooter(); ?>
</body>
</html>
