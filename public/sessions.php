<?php 
    require_once __DIR__ . '/../src/auth.php';
    require_once __DIR__ . '/../src/models/class_sessions.php';
    require_once __DIR__ . '/../src/templates/class_session_card.php';


    requireLogin();

    $today = "2026-05-01"; // hardcoded "todays date" for development
    $selected_date = $_GET['date'] ?? $today;

    // Calculate Monday of the week for the reference 'today'
    $monday = date('Y-m-d', strtotime('monday this week', strtotime($today)));
    $week_days = [];
    for ($i = 0; $i < 7; $i++) {
        $timestamp = strtotime("$monday +$i days");
        $week_days[] = [
            'date' => date('Y-m-d', $timestamp),
            'day_name' => date('D', $timestamp),
            'day_num' => date('j', $timestamp)
        ];
    }

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

    <nav class="week-selector">
        <?php foreach ($week_days as $day): ?>
            <a href="?date=<?= $day['date'] ?>" 
               class="day-item <?= $day['date'] === $selected_date ? 'active' : '' ?> <?= $day['date'] === $today ? 'today-highlight' : '' ?>">
                <span class="day-name"><?= $day['day_name'] ?></span>
                <span class="day-date"><?= $day['day_num'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <main class="card-container-vertical" id="sessions-container">
        <?php foreach ($sessions as $session): ?>
            <?php outputClassSessionCard($session) ?>
        <?php endforeach; ?>
    </main>
</body>
</html>