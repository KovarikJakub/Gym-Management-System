<?php 
    require_once __DIR__ . '/../src/auth.php';
    require_once __DIR__ . '/../src/Utils/helpers.php';
    require_once __DIR__ . '/../src/models/class_sessions.php';
    require_once __DIR__ . '/../src/models/users.php';
    require_once __DIR__ . '/../src/models/gym_classes.php';
    require_once __DIR__ . '/../src/templates/class_session_card.php';
    require_once __DIR__ . '/../src/templates/filter_tab.php';
    require_once __DIR__ . '/../src/templates/week_selector.php';
    require_once __DIR__ . '/../src/templates/common.php';

    requireLogin();

    $user_id = $_SESSION['user_id'];
    $today = "2026-06-02"; // hardcoded "todays date" for development
    $selected_date = $_GET['date'] ?? $today;
    $selected_date = date("Y-m-d", strtotime($selected_date));

    // Get active filters from GET
    $filters = [
        'type' => $_GET['type'] ?? '',
        'trainer_id' => $_GET['trainer_id'] ?? ''
    ];

    $week_days = getWeekDays($today);
    $trainers = getAllTrainers();
    $class_types = getAllClassTypes();

    $sessions = getFilteredSessions($selected_date, $user_id, $filters);
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
    <link rel="stylesheet" href="css/templates/session_card.css">
    <link rel="stylesheet" href="css/templates/filter_tab.css">
    <link rel="stylesheet" href="css/templates/user_badge.css">

    <link rel="stylesheet" href="css/sessions.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <script src="js/sessions.js" defer></script>
</head>
<body>
    <?php outputHeader(); ?>
    <main>
        <?php outputFilterTab($week_days, $selected_date, $today, $trainers, $class_types, $filters); ?>
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