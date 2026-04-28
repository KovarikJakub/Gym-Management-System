<?php 
    require_once __DIR__ . '/../src/models/class_sessions.php';
    $date="2026-05-02";
    # call getFilteredSessions with todays date to obtain sessions for today
    # $sessions = getFilteredSessions($date);
    
    # temporary! - this gets all the sessions:
    $sessions = getAllSessions();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule - Gym 2026</title>
    <link rel="stylesheet" href="css/card.css">
    <link rel="stylesheet" href="css/sessions.css">
    <script src="js/sessions.js" defer></script>
</head>
<body>
    <header class="main-header">
        <h1>Weekly Schedule</h1>
    </header>

    <main class="card-container">
        <?php foreach ($sessions as $session): ?>
        <article class="card <?= $session['is_full'] ? 'card-disabled' : '' ?>">
            <header class="card-header">
                <h2 class="card-title"><?= htmlspecialchars($session['class_name']) ?></h2>
                <span class="badge badge-<?= str_replace(' ', '-', $session['class_type']) ?>">
                    <?= htmlspecialchars($session['class_type']) ?>
                </span>
            </header>

            <div class="card-body">
                <p class="session-time">
                    <strong>Time:</strong> <?= date('M j, H:i', strtotime($session['start_time'])) ?>
                </p>
                <p class="session-trainer">
                    <strong>Trainer:</strong> <?= htmlspecialchars($session['trainer_name']) ?>
                </p>
                
                <div class="card-details">
                    <div class="detail-item">
                        <strong>Availability</strong>
                        <span><?= $session['current_enrollment'] ?> / <?= $session['default_capacity'] ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Status</strong>
                        <span class="<?= $session['is_full'] ? 'text-danger' : 'text-success' ?>">
                            <?= $session['is_full'] ? 'Full' : $session['spots_left'] . ' left' ?>
                        </span>
                    </div>
                </div>

                <?php if (!$session['is_full']): ?>
                    <button class="btn-enroll" data-id="<?= $session['session_id'] ?>" >Enroll Now</button>
                <?php else: ?>
                    <button class="btn-enroll" disabled>Session is full</button>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </main>
</body>
</html>