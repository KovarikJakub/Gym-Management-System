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

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!verifyCsrf($_POST['csrf_token'] ?? ''))    
        die("CSRF token validation failed.");

    $realName = $_POST['real_name'];
    $username = $_POST['username'];
    $password =  !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $profilePhoto = null;

    // Handle File Upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE)
    {
        $uploadResult = validateAndSaveImage($_FILES['profile_photo'], $uploadDir); 

        if (isset($uploadResult['error']))
            $error = $uploadResult['error'];
        else
            $profilePhoto = $uploadResult['filename'];
    }

    if (!$error)
    {

        try
        {
        // Update user row in database:
        updateUser($_SESSION['user_id'], $realName, $username, $password, $profilePhoto);
        
        // Delete previous profile photo if file upload happened and previous profile photo is not just a placeholder:
            if ($profilePhoto && !isNullOrEmptyString($user['profile_photo']))
            removeFileIfExists($uploadDir . $user['profile_photo']);

        // Update session in case username changed
        $_SESSION['username'] = $username;
        
        // Refresh user data
        $user = getUserById($_SESSION['user_id']);
        $success = "Profile updated successfully!";
    }
    catch (PDOException $e)
    {
        if (strpos($e->getMessage(), 'users.username') !== false)
            $error = "Username already exists.";
        else
            $error = "An error occurred. Please try again.";
        // Cleanup uploaded photo if update fails
        if ($profilePhoto && !isNullOrEmptyString($user['profile_photo']))
        removeFileIfExists($uploadDir . $profilePhoto);
    }   
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Gym Management</title>
</head>
<body>
    <div class="profile-container">
        <h1>Edit Profile</h1>
        
        <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($success): ?><p style="color:green;"><?= htmlspecialchars($success) ?></p><?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="form-group">
                <label for="real_name">Full Name:</label>
                <input type="text" name="real_name" id="real_name" value="<?= htmlspecialchars($user['real_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">New Password (leave blank to keep current):</label>
                <input type="password" name="password" id="password">
            </div>

            <div class="form-group">
                <label for="profile_photo">Profile Photo:</label>
                <?php if ($user['profile_photo']): ?>
                    <div class="current-photo">
                        <img src="<?= "http://localhost:9000/public/uploads/" . htmlspecialchars($user['profile_photo']) ?>" alt="Profile Photo" style="max-width: 150px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="profile_photo" id="profile_photo" accept="image/*">
            </div>

            <button type="submit">Update Profile</button>
        </form>
        
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
