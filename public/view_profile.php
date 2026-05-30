<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php';
require_once __DIR__ . '/../src/templates/common.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = getUserById($id);

if (!$user) {
    // If user not found, we could redirect or show an error.
    // Let's show a simple error message within the layout.
    $error = "User not found.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/view_profile.css" rel="stylesheet">
    <title><?= $user ? htmlspecialchars($user['username']) . "'s Profile" : "User Not Found" ?> - Fit-Pro</title>
</head>
<body>
    <?php outputHeader(); ?>

    <main class="center-content">
        <div class="profile-view boxes">
            <?php if (!$user): ?>
                <div class="overlay">
                    <h1>User Not Found</h1>
                    <p>The user you are looking for does not exist or has been removed.</p>
                    <p><a href="index.html" class="btn">Go Home</a></p>
                </div>
            <?php else: ?>
                <div class="profile-header">
                    <?php 
                        $photo = !empty($user['profile_photo']) ? 'uploads/' . $user['profile_photo'] : 'uploads/placeholder.jpg';
                    ?>
                    <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($user['username']) ?>" class="profile-photo">
                    <div class="profile-info">
                        <h1><?= htmlspecialchars($user['real_name']) ?></h1>
                        <p class="username">@<?= htmlspecialchars($user['username']) ?></p>
                    </div>
                </div>
                <div class="profile-details">
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Role:</strong> <?= ucfirst(htmlspecialchars($user['role'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php outputFooter(); ?>
</body>
</html>