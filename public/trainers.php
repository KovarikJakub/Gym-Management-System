<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/trainers.php';
require_once __DIR__ . '/../src/templates/common.php';

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
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/templates/session_card.css">
    <link rel="stylesheet" href="css/trainer.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <?php outputHeader(); ?>

    <main class="card-container trainers-container">
        <?php if (empty($trainers)): ?>
            <p>No trainers found.</p>
        <?php endif; ?>

        <?php foreach ($trainers as $trainer): ?>
            <article class="card trainer-card">
                <img
                    class="trainer-card-photo"
                    src="<?= htmlspecialchars($uploadsBaseUrl . ($trainer['profile_photo'] ?: 'placeholder.jpg')) ?>"
                    alt="<?= htmlspecialchars($trainer['real_name']) ?>"
                >

                <div class="trainer-card-body">
                    <h2 class="card-title"><?= htmlspecialchars($trainer['real_name']) ?></h2>

                    <p class="trainer-card-bio">
                        <?= htmlspecialchars($trainer['bio'] ?? 'No bio provided yet.') ?>
                    </p>

                    <div class="trainer-card-specializations">
                        <strong>Specializations</strong>
                        <p><?= htmlspecialchars($trainer['specializations'] ?? 'No specializations listed.') ?></p>
                    </div>

                    <a href="trainer.php?id=<?= $trainer['trainer_id'] ?>" class="btn">
                        View Profile
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </main>
</body>
</html>
