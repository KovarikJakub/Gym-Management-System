<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/trainers.php';

requireLogin();

$trainers = getAllTrainerProfiles();
$uploadsBaseUrl = 'uploads/';
$defaultPhoto = 'uploads/placeholder.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainers - Gym Management</title>
    <link rel="stylesheet" href="css/card.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <header>
        <div>
            <h1><a href="index.html">Fit-Pro</a></h1>
        </div>

        <nav id="menu">
            <input type="checkbox" id="hamburger">
            <label class="hamburger" for="hamburger"></label>
            <ul>
                <li><a href="classes.html">Classes</a></li>
                <li><a href="equipment.html">Equipment</a></li>
                <li><a href="trainers.php">Trainers</a></li>
                <li><a href="schedule.html">Schedule</a></li>
            </ul>
        </nav>

        <div id="signup">
            <a href="profile.php"><h2>Profile</h2></a>
            <a href="logout.php"><h2>Logout</h2></a>
        </div>
    </header>

    <main class="card-container trainers-container">
        <?php if (empty($trainers)): ?>
            <p>No trainers found.</p>
        <?php endif; ?>

        <?php foreach ($trainers as $trainer): ?>
            <?php
                $classes = getTrainerClasses($trainer['trainer_id']);
                $certificates = getTrainerCertificates($trainer['trainer_id']);
            ?>
            <article class="card">
                <header class="card-header">
                    <h2 class="card-title"><?= htmlspecialchars($trainer['real_name']) ?></h2>
                    <span class="badge badge-trainer">Trainer</span>
                </header>

                <div class="card-body">
                    <div class="trainer-photo">
                        <img
                            src="<?= htmlspecialchars($uploadsBaseUrl . ($trainer['profile_photo'] ?: 'placeholder.jpg')) ?>"
                            alt="<?= htmlspecialchars($trainer['real_name']) ?>"
                            style="max-width: 160px; width: 100%; height: auto;"
                        >
                        <?php if (($_SESSION['role'] ?? null) === 'trainer' && (int)$trainer['trainer_id'] === (int)$_SESSION['user_id']): ?>
                            <a href="profile.php" class="btn trainer-profile-btn">View Profile</a>
                        <?php endif; ?>
                    </div>

                    <p><strong>Username:</strong> <?= htmlspecialchars($trainer['username']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($trainer['email']) ?></p>

                    <div class="trainer-section">
                        <strong>Bio</strong>
                        <p><?= htmlspecialchars($trainer['bio'] ?? 'No bio provided yet.') ?></p>
                    </div>

                    <div class="trainer-section">
                        <strong>Specializations</strong>
                        <p><?= htmlspecialchars($trainer['specializations'] ?? 'No specializations listed.') ?></p>
                    </div>

                    <div class="trainer-section">
                        <strong>Certifications</strong>
                        <p><?= htmlspecialchars($trainer['certifications'] ?? 'No certifications summary listed.') ?></p>
                    </div>

                    <div class="trainer-section">
                        <strong>Classes Taught</strong>
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
                    </div>

                    <div class="trainer-section">
                        <strong>Certification Files</strong>
                        <?php if (empty($certificates)): ?>
                            <p>No certification files uploaded.</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($certificates as $certificate): ?>
                                    <li>
                                        <a href="<?= htmlspecialchars($certificate['file_path']) ?>" target="_blank" rel="noopener noreferrer">
                                            <?= htmlspecialchars($certificate['title']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </main>
</body>
</html>
