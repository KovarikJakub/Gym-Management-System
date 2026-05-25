<?php 
    require_once __DIR__ . '/../src/auth.php';
    require_once __DIR__ . '/../src/Utils/helpers.php';
    require_once __DIR__ . '/../src/models/class_sessions.php';
    require_once __DIR__ . '/../src/templates/class_session_card.php';
    require_once __DIR__ . '/../src/templates/week_selector.php';
    require_once __DIR__ . '/../src/templates/common.php';

    requireLogin();

    $user_id = $_SESSION['user_id'];
    $today = "2026-05-02"; // hardcoded "todays date" for development
    $selected_date = $_GET['date'] ?? $today;
    $selected_date = date("Y-m-d", strtotime($selected_date));

    $week_days = getWeekDays($today);
    $sessions = getFilteredSessions($selected_date, $user_id);
?>

<?php function isSelectedDayInThePast($selected_date, $today)
{
    return ($selected_date < $today);
}?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    <title>Class Schedule - Fit-Pro</title>
    <link rel="stylesheet" href="css/card.css">
    <link rel="stylesheet" href="css/sessions.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <script src="js/sessions.js" defer></script>
</head>
<body>
    <?php outputHeader(); ?>

    <main>
        <header class="main-header">
            <h1>Weekly Schedule</h1>
        </header>
        
        <?php outputWeekSelector($week_days, $selected_date, $today); ?>

        <?php if (isSelectedDayInThePast($selected_date, $today)): ?>
            <div class="alert alert-warning">
                <strong>Note:</strong> You are viewing a date in the past. New enrollments are disabled for these sessions.
            </div>
        <?php endif ?>

        <?php if (empty($sessions)): ?>
            <div class="alert alert-info">
                <strong>Note:</strong> There are no sessions for this day.
            </div>
        <?php endif ?>

        <div class="card-container-vertical" id="sessions-container">
            <?php foreach ($sessions as $session): ?>
                <?php outputClassSessionCard($session, $today) ?>
            <?php endforeach; ?>
        </div>
    </main>

    <?php outputFooter(); ?>
</body>
</html>