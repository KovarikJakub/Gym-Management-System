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
    $isAdmin = $_SESSION['role'] === 'admin'; 

    $today = "2026-05-02"; 
    $selected_date = $_GET['date'] ?? $today;
    $selected_date = date("Y-m-d", strtotime($selected_date));

    
    $filters = [
        'type' => $_GET['type'] ?? '',
        'trainer_id' => $_GET['trainer_id'] ?? ''
    ];

    $week_days = getWeekDays($today);
    $trainers = getAllTrainers();
    $class_types = getAllClassTypes();

    $sessions = getFilteredSessions($selected_date, $user_id, $filters);

    if ($isAdmin) {
        $db = getDbConnection(); 
        $classTemplates = $db->query("
            SELECT id, name, type, difficulty, default_capacity 
            FROM gym_classes 
            ORDER BY name ASC
        ")->fetchAll();
    }

    function isSelectedDayInThePast($selected_date, $today) {
        return ($selected_date < $today);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Class Schedule - Fit-Pro</title>
    <link rel="stylesheet" href="css/card.css">
    <link rel="stylesheet" href="css/sessions.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/admin.css"> <script src="js/sessions.js" defer></script>
</head>
<body>
    <?php outputHeader(); ?>
    <main>

        <?php if ($isAdmin): ?>
        <section style="padding: 2rem; display: flex; gap: 1rem; justify-content: center;">
            <button class="btn active" onclick="switchAdminTab('view-schedule')" id="btn-view-schedule">1. View Schedule</button>
            <button class="btn" onclick="switchAdminTab('add-session')" id="btn-add-session">2. Schedule Session</button>
        </section>
        <?php endif; ?>

        <div id="view-schedule" class="admin-tab active" style="display: block;">
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
        </div>

        <?php if ($isAdmin): ?>
            
            <div id="add-session" class="admin-tab" style="display: none; padding: 2rem;">
                <article class="boxes">
                    <h2>Schedule a Session</h2>
                    <form class="admin-form" id="sessionForm">
                        <label class="admin-label">Select Class Type
                            <select class="admin-input admin-select" name="class_id" required>
                                <?php foreach ($classTemplates as $class): ?>
                                    <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="admin-label">Assign Trainer
                            <select class="admin-input admin-select" name="trainer_id" required>
                                <?php foreach ($trainers as $trainer): ?>
                                    <option value="<?= $trainer['id'] ?>"><?= htmlspecialchars($trainer['real_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="admin-label">Date & Time
                            <input class="admin-input" type="datetime-local" name="start_time" required>
                        </label>

                        <button type="submit" class="btn">Publish to Schedule</button>
                    </form>
                </article>
            </div>

            <script>
                function switchAdminTab(tabId) {
                    document.querySelectorAll('.admin-tab').forEach(el => el.style.display = 'none');
                    document.getElementById(tabId).style.display = 'block';
                    document.querySelectorAll('button[id^="btn-"]').forEach(btn => btn.style.opacity = '0.5');
                    document.getElementById('btn-' + tabId).style.opacity = '1';
                }
            </script>
        <?php endif; ?>

    </main>
    <?php outputFooter(); ?>
</body>
</html>