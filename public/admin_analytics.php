<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/admin_analytics.php';
require_once __DIR__ . '/../src/templates/common.php';
require_once __DIR__ . '/../src/templates/admin_header.php';
require_once __DIR__ . '/../src/templates/admin_containers.php';

requireLogin();

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$summary = getAdminAnalyticsSummary();
$popularClasses = getMostPopularClasses();
$trainerAnalytics = getTrainerAnalytics();

function outputAnalyticsValue($value, $fallback = '0')
{
    echo htmlspecialchars($value === null ? $fallback : (string)$value);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fit-Pro - Admin Analytics</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <?php outputHeader(); ?>

    <main>
        <?php outputAdminHeader('Admin Analytics', 'A quick look at what is happening in the gym'); ?>

        <section id="info" class="analytics-dashboard">
            <article class="boxes analytics-card">
                <h2>Members</h2>
                <strong><?php outputAnalyticsValue($summary['total_members']); ?></strong>
                <span>registered accounts</span>
            </article>

            <article class="boxes analytics-card">
                <h2>Trainers</h2>
                <strong><?php outputAnalyticsValue($summary['total_trainers']); ?></strong>
                <span>active profiles</span>
            </article>

            <article class="boxes analytics-card">
                <h2>Classes</h2>
                <strong><?php outputAnalyticsValue($summary['total_classes']); ?></strong>
                <span>catalog entries</span>
            </article>

            <article class="boxes analytics-card">
                <h2>Bookings</h2>
                <strong><?php outputAnalyticsValue($summary['total_enrollments']); ?></strong>
                <span>total enrollments</span>
            </article>

            <?php outputAdminBoxStart('Most Popular Classes', '', 'admin-table-box analytics-full-width'); ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Type</th>
                            <th>Sessions</th>
                            <th>Bookings</th>
                            <th>Avg Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($popularClasses as $class): ?>
                        <tr>
                            <td><?= htmlspecialchars($class['name']) ?></td>
                            <td class="text-capitalize"><?= htmlspecialchars($class['type']) ?></td>
                            <td><?= (int)$class['sessions_count'] ?></td>
                            <td><?= (int)$class['enrollments_count'] ?></td>
                            <td><?= htmlspecialchars($class['average_rating'] ?? 'No reviews') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php outputAdminBoxEnd(); ?>

            <?php outputAdminBoxStart('Trainer Performance', '', 'admin-table-box analytics-full-width'); ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Trainer</th>
                            <th>Sessions</th>
                            <th>Bookings</th>
                            <th>Avg Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trainerAnalytics as $trainer): ?>
                        <tr>
                            <td><?= htmlspecialchars($trainer['trainer_name']) ?></td>
                            <td><?= (int)$trainer['sessions_count'] ?></td>
                            <td><?= (int)$trainer['enrollments_count'] ?></td>
                            <td><?= htmlspecialchars($trainer['average_rating'] ?? 'No reviews') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php outputAdminBoxEnd(); ?>

        </section>
    </main>

    <?php outputFooter(); ?>
</body>
</html>
