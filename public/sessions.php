<?php 
    require_once __DIR__ . '/../src/auth.php';
    require_once __DIR__ . '/../src/Utils/helpers.php';
    require_once __DIR__ . '/../src/models/class_sessions.php';
    require_once __DIR__ . '/../src/templates/class_session_card.php';
    require_once __DIR__ . '/../src/templates/week_selector.php';


    requireLogin();

    $today = "2026-05-01"; // hardcoded "todays date" for development
    $selected_date = $_GET['date'] ?? $today;

    $week_days = getWeekDays($today);

    $sessions = getFilteredSessions($selected_date);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    <title>Class Schedule - Gym 2026</title>
    <link rel="stylesheet" href="css/card.css">
    <link rel="stylesheet" href="css/sessions.css">
    <script src="js/sessions.js" defer></script>
</head>
<body>
    <header class="main-header">
        <h1>Weekly Schedule</h1>
    </header>

    <?php outputWeekSelector($week_days, $selected_date, $today); ?>

    <main class="card-container-vertical" id="sessions-container">
        <?php foreach ($sessions as $session): ?>
            <?php outputClassSessionCard($session) ?>
        <?php endforeach; ?>
    </main>
</body>
</html>