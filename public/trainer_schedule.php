<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/trainers.php';
require_once __DIR__ . '/../src/templates/common.php';

requireRole('trainer');

$trainerId = $_SESSION['user_id'];
$sessions = getTrainerSessions($trainerId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - Gym Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/trainer_schedule.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <?php outputHeader(); ?>

    <main class="card-container">
        <?php if (empty($sessions)): ?>
            <p>No sessions assigned to you.</p>
        <?php endif; ?>

        <?php foreach ($sessions as $session): ?>
            <?php $members = getEnrolledMembersForSession($session['session_id'], $trainerId); ?>

            <article class="card">
                <header class="card-header">
                    <h2 class="card-title"><?= htmlspecialchars($session['class_name']) ?></h2>
                    <span class="badge badge-<?= str_replace(' ', '-', htmlspecialchars($session['class_type'])) ?>">
                        <?= htmlspecialchars($session['class_type']) ?>
                    </span>
                </header>

                <div class="card-body">
                    <p><strong>Start:</strong> <?= htmlspecialchars(date('M j, H:i', strtotime($session['start_time']))) ?></p>
                    <p><strong>End:</strong> <?= htmlspecialchars(date('M j, H:i', strtotime($session['end_time']))) ?></p>
                    <p><strong>Enrollment:</strong> <?= htmlspecialchars($session['current_enrollment']) ?> / <?= htmlspecialchars($session['default_capacity']) ?></p>

                    <h3>Enrolled Members</h3>
                    <?php if (empty($members)): ?>
                        <p>No members enrolled yet.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($members as $member): ?>
                                <li>
                                    <?= htmlspecialchars($member['real_name']) ?>
                                    (<?= htmlspecialchars($member['username']) ?>,
                                    <?= htmlspecialchars($member['email']) ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </main>
</body>
</html>
