<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/trainers.php';
require_once __DIR__ . '/../src/templates/common.php';

$trainerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$trainer = $trainerId > 0 ? getTrainerProfile($trainerId) : null;
$certificates = $trainer ? getTrainerCertificates($trainerId) : [];
$classes = $trainer ? getTrainerClasses($trainerId) : [];
$uploadsBaseUrl = 'uploads/';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $trainer ? htmlspecialchars($trainer['real_name']) : 'Trainer Not Found' ?> - Fit-Pro</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/trainer.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <?php outputHeader(); ?>

    <main class="trainer-profile-page">
        <?php if (!$trainer): ?>
            <section class="trainer-profile trainer-profile-empty">
                <h1>Trainer Not Found</h1>
                <p>The trainer you are looking for does not exist or is unavailable.</p>
                <a href="trainers.php" class="btn">Back to Trainers</a>
            </section>
        <?php else: ?>
            <section class="trainer-profile">
                <div class="trainer-profile-hero">
                    <img
                        class="trainer-profile-photo"
                        src="<?= htmlspecialchars($uploadsBaseUrl . ($trainer['profile_photo'] ?: 'placeholder.jpg')) ?>"
                        alt="<?= htmlspecialchars($trainer['real_name']) ?>"
                    >

                    <div class="trainer-profile-heading">
                        <h1><?= htmlspecialchars($trainer['real_name']) ?></h1>
                        <p>@<?= htmlspecialchars($trainer['username']) ?></p>
                        <p><?= htmlspecialchars($trainer['email']) ?></p>
                    </div>
                </div>

                <div class="trainer-profile-grid">
                    <section class="trainer-profile-section">
                        <h2>Bio</h2>
                        <p><?= htmlspecialchars($trainer['bio'] ?? 'No bio provided yet.') ?></p>
                    </section>

                    <section class="trainer-profile-section">
                        <h2>Specializations</h2>
                        <p><?= htmlspecialchars($trainer['specializations'] ?? 'No specializations listed.') ?></p>
                    </section>

                    <section class="trainer-profile-section">
                        <h2>Certifications</h2>
                        <p><?= htmlspecialchars($trainer['certifications'] ?? 'No certifications summary listed.') ?></p>
                    </section>

                    <section class="trainer-profile-section">
                        <h2>Classes Taught</h2>
                        <?php if (empty($classes)): ?>
                            <p>No assigned classes yet.</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($classes as $class): ?>
                                    <li>
                                        <?= htmlspecialchars($class['class_name']) ?>
                                        <span>(<?= htmlspecialchars($class['session_count']) ?> session<?= ((int)$class['session_count'] === 1) ? '' : 's' ?>)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </section>

                    <section class="trainer-profile-section trainer-profile-files">
                        <h2>Certification Files</h2>
                        <?php if (empty($certificates)): ?>
                            <p>No certification files uploaded.</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($certificates as $certificate): ?>
                                    <li>
                                        <a href="<?= htmlspecialchars($certificate['file_path']) ?>" target="_blank" rel="noopener noreferrer">
                                            <?= htmlspecialchars($certificate['title']) ?>
                                        </a>
                                        <span><?= htmlspecialchars($certificate['original_name']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </section>
                </div>

                <a href="trainers.php" class="btn trainer-back-btn">Back to Trainers</a>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
