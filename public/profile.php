<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php';
require_once __DIR__ . '/../src/models/trainers.php';
require_once __DIR__ . '/../src/Utils/upload.php';
require_once __DIR__ . '/../src/Utils/helpers.php';

requireLogin();

$user = getUserById($_SESSION['user_id']);
$isTrainer = ($user['role'] ?? null) === 'trainer';
$trainerProfile = $isTrainer ? getTrainerProfile($_SESSION['user_id']) : null;
$trainerCertificates = $isTrainer ? getTrainerCertificates($_SESSION['user_id']) : [];
$uploadDir = __DIR__ . '/uploads/';
$certificatesDir = __DIR__ . '/uploads/certifications/';
$error = null;
$success = null;

$uploadsBaseUrl = 'uploads/'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    try
    {
        if (!verifyCsrf($_POST['csrf_token'] ?? ''))
            throw new Exception("CSRF token validation failed. Please try again.");

        $action = $_POST['action'] ?? 'update_profile';

        if ($action === 'delete_certificate')
        {
            if (!$isTrainer)
                throw new Exception("Only trainers can manage certification files.");

            $certificateId = (int)($_POST['certificate_id'] ?? 0);
            if ($certificateId <= 0)
                throw new Exception("Invalid certification file.");

            $certificate = getTrainerCertificateById($_SESSION['user_id'], $certificateId);
            if (!$certificate)
                throw new Exception("Certification file not found.");

            if (!deleteTrainerCertificate($_SESSION['user_id'], $certificateId))
                throw new Exception("Could not delete certification file.");

            removeFileIfExists($certificatesDir . $certificate['file_name']);
            $trainerCertificates = getTrainerCertificates($_SESSION['user_id']);
            $success = "Certification file deleted successfully.";
        }
        elseif ($action === 'upload_certificates')
        {
            if (!$isTrainer)
                throw new Exception("Only trainers can upload certification files.");

            try
            {
                $files = normalizeUploadedFiles($_FILES['certification_files'] ?? []);
                if (empty($files))
                    throw new Exception("Please choose at least one certification file.");

                $savedCertificates = [];

                foreach ($files as $file)
                {
                    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)
                        continue;

                    $uploadResult = validateAndSaveCertificateFile($file, $certificatesDir, 5242880);
                    if (isset($uploadResult['error']))
                        throw new Exception($uploadResult['error']);

                    $savedCertificates[] = $uploadResult;
                }

                if (empty($savedCertificates))
                    throw new Exception("Please choose at least one certification file.");

                $db = getDbConnection();
                $db->beginTransaction();

                try
                {
                    foreach ($savedCertificates as $savedCertificate)
                    {
                        $title = pathinfo($savedCertificate['original_name'], PATHINFO_FILENAME);
                        addTrainerCertificate(
                            $_SESSION['user_id'],
                            $title,
                            $savedCertificate['original_name'],
                            $savedCertificate['filename'],
                            'uploads/certifications/' . $savedCertificate['filename'],
                            $savedCertificate['mime_type']
                        );
                    }

                    $db->commit();
                }
                catch (Exception $e)
                {
                    if ($db->inTransaction())
                        $db->rollBack();

                    foreach ($savedCertificates as $savedCertificate)
                        removeFileIfExists($certificatesDir . $savedCertificate['filename']);

                    throw $e;
                }

                $trainerCertificates = getTrainerCertificates($_SESSION['user_id']);
                $success = "Certification file(s) uploaded successfully.";
            }
            catch (Exception $e)
            {
                if (isset($savedCertificates) && is_array($savedCertificates))
                {
                    foreach ($savedCertificates as $savedCertificate)
                        removeFileIfExists($certificatesDir . $savedCertificate['filename']);
                }

                throw $e;
            }
        }
        else
        {
        $realName = trim($_POST['real_name']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $deletePhoto = isset($_POST['delete_photo']);
        $profilePhoto = null;
        $bio = trim($_POST['bio'] ?? '');
        $specializations = trim($_POST['specializations'] ?? '');
        $certifications = trim($_POST['certifications'] ?? '');

        if (empty($realName) || strlen($realName) > 255)
            throw new Exception("Invalid full name.");
        
        if (empty($username) || strlen($username) > 255)
            throw new Exception("Invalid username.");

        if ($isTrainer && (strlen($bio) > 2000 || strlen($specializations) > 1000 || strlen($certifications) > 1000))
            throw new Exception("Trainer profile fields are too long.");

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

            if ($isTrainer)
                upsertTrainerProfile($_SESSION['user_id'], $bio, $specializations, $certifications);
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
        $isTrainer = ($user['role'] ?? null) === 'trainer';
        $trainerProfile = $isTrainer ? getTrainerProfile($_SESSION['user_id']) : null;
        $trainerCertificates = $isTrainer ? getTrainerCertificates($_SESSION['user_id']) : [];
        $success = "Profile updated successfully!";
        }

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
    <title>Edit Profile - Gym Management</title>
</head>
<body>
    <div class="profile-container">
        <h1>Edit Profile</h1>
        <p><a href="index.php">Back to Home</a></p>
        
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
                <?php if (!isNullOrEmptyString($user['profile_photo'])): ?>
                    <div class="current-photo">
                        <img src="<?= $uploadsBaseUrl . htmlspecialchars($user['profile_photo']) ?>" alt="Profile Photo" style="max-width: 150px;">
                        <div>
                            <input type="checkbox" name="delete_photo" id="delete_photo">
                            <label for="delete_photo">Remove current photo</label>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" name="profile_photo" id="profile_photo" accept="image/*">
            </div>

            <?php if ($isTrainer): ?>
                <fieldset>
                    <legend>Trainer Profile</legend>

                    <p><a href="trainer_schedule.php">My Schedule</a></p>

                    <div class="form-group">
                        <label for="bio">Bio:</label>
                        <textarea name="bio" id="bio" rows="5"><?= htmlspecialchars($trainerProfile['bio'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="specializations">Specializations:</label>
                        <textarea name="specializations" id="specializations" rows="3"><?= htmlspecialchars($trainerProfile['specializations'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="certifications">Certifications:</label>
                        <textarea name="certifications" id="certifications" rows="3"><?= htmlspecialchars($trainerProfile['certifications'] ?? '') ?></textarea>
                    </div>
                </fieldset>
            <?php endif; ?>

            <button type="submit">Update Profile</button>
        </form>

        <?php if ($isTrainer): ?>
            <fieldset>
                <legend>Certification Files</legend>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="upload_certificates">

                    <div class="form-group">
                        <label for="certification_files">Upload certification files:</label>
                        <input type="file" name="certification_files[]" id="certification_files" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg" multiple>
                        <p>Allowed files: PDF, PNG, JPG, JPEG. Max size per file: 5MB.</p>
                    </div>

                    <button type="submit">Upload Files</button>
                </form>

                <div class="trainer-certificates">
                    <?php if (empty($trainerCertificates)): ?>
                        <p>No certification files uploaded yet.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($trainerCertificates as $certificate): ?>
                                <li>
                                    <a href="<?= htmlspecialchars($certificate['file_path']) ?>" target="_blank" rel="noopener noreferrer">
                                        <?= htmlspecialchars($certificate['title']) ?>
                                    </a>
                                    <span><?= htmlspecialchars($certificate['original_name']) ?></span>
                                    <span><?= htmlspecialchars($certificate['uploaded_at']) ?></span>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="delete_certificate">
                                        <input type="hidden" name="certificate_id" value="<?= htmlspecialchars($certificate['id']) ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </fieldset>
        <?php endif; ?>
        
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
