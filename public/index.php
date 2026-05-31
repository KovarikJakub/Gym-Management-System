<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/templates/common.php';
require_once __DIR__ . '/../src/database.php';

$db = getDbConnection();

// Fetch next 2 sessions including trainer info
$sessions_sql = "SELECT 
                    s.id AS session_id,
                    c.name AS class_name,
                    c.type AS class_type,
                    u.real_name AS trainer_name,
                    u.profile_photo AS trainer_profile_photo,
                    u.id AS trainer_id,
                    s.start_time
                FROM class_sessions s
                JOIN gym_classes c ON s.class_id = c.id
                JOIN users u ON s.trainer_id = u.id
                WHERE s.start_time >= CURRENT_TIMESTAMP
                ORDER BY s.start_time ASC
                LIMIT 3";
$next_sessions = $db->query($sessions_sql)->fetchAll();

// Fetch 3 available equipment items
$equipment_sql = "SELECT name, status FROM equipment WHERE status = 'Functional' LIMIT 3";
$available_equipment = $db->query($equipment_sql)->fetchAll();

$uploadsBaseUrl = 'uploads/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym managment</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/templates/user_badge.css" rel="stylesheet">
</head>
<body>
    <?php outputHeader(); ?>

    <main class="home-main">
        <section id="presentation">
            <div class="overlay">
                <h1>Welcome to Fit-Pro</h1>
                <p>Your only limit is you mind!</p>
            </div>
        </section>

        <section id="shortcuts">
            <article class="boxes">
                <h3>Classes</h3>
                <ul>
                    <?php if (empty($next_sessions)): ?>
                        <li>No upcoming classes.</li>
                    <?php else: ?>
                        <?php foreach ($next_sessions as $session): ?>
                            <li><?= htmlspecialchars($session['class_name']) ?> - <?= date('M j, H:i', strtotime($session['start_time'])) ?></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <br>
                <a href="sessions.php" class="btn">View Classes Schedule</a>
            </article>

            <article class="boxes">
                <h3>Equipment</h3>
                <ul>
                    <?php if (empty($available_equipment)): ?>
                        <li>No equipment available.</li>
                    <?php else: ?>
                        <?php foreach ($available_equipment as $eq): ?>
                            <li><?= htmlspecialchars($eq['name']) ?> - <?= htmlspecialchars($eq['status']) ?></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <br>
                <a href="equipment.php" class="btn">Check Equipment</a>
            </article>

            <article class="boxes">
                <h3>Trainers</h3>
                <div class="home-trainers-list">
                    <?php if (empty($next_sessions)): ?>
                        <p>No trainers scheduled.</p>
                    <?php else: ?>
                        <?php foreach (array_slice($next_sessions, 0, 2) as $session): ?>
                            <div class="home-trainer-item" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <img 
                                    src="<?= htmlspecialchars($uploadsBaseUrl . ($session['trainer_profile_photo'] ?: 'placeholder.jpg')) ?>" 
                                    alt="<?= htmlspecialchars($session['trainer_name']) ?>"
                                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid var(--title-color);"
                                >
                                <span style="color: var(--font-color); font-weight: bold;"><?= htmlspecialchars($session['trainer_name']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <br>
                <a href="trainers.php" class="btn">Book Trainers</a>
            </article>
            <br>
        </section>
        <section id="info">
            <h1></h1>
            <article class="boxes">
                <h2>Zones</h2>
                <ul>
                    <li>Cardio Zone</li>
                    <li>Yoga Room</li>
                    <li>Strength Hall</li>
                </ul>
            </article>
            <article class="boxes">
                <h2>Opening Hours</h2>
                <ul>
                    <li>Monday-Friday: 6:00 - 22:00</li>
                    <li>Saturday: 8:00 - 20:00</li>
                    <li>Sunday: 8:00 - 18:00</li>
                </ul>
            </article>
            <br><br><br>
        </section>
    </main>
    <?php outputFooter(); ?>
</body>
</html>
