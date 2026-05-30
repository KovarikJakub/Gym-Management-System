<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/class_sessions.php';
require_once __DIR__ . '/../src/templates/past_sessions.php';
require_once __DIR__ . '/../src/templates/pager.php';
require_once __DIR__ . '/../src/templates/common.php';

requireLogin();

$user_id = $_SESSION['user_id'];

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$page_size = 5;
$offset = ($page - 1) * $page_size;

$past_sessions = getUsersPastSessions($user_id, $page_size, $offset);
$total_past_sessions = countUsersPastSessions($user_id);
$total_pages = ceil($total_past_sessions / $page_size);

$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Fit-Pro</title>
    <link href="css/templates/review_form.css" rel="stylesheet">
    <link href="css/templates/past_session.css" rel="stylesheet">
    <link href="css/templates/user_badge.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
</head>
<body>
    <?php outputHeader(); ?>
    <main>
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
        
        <?php if ($success): ?>
            <p style="color: green;"><?= htmlspecialchars($success === 'review_posted' ? 'Review posted successfully!' : $success) ?></p>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <p>This is a protected dashboard area.</p>
        
        <section class="past-sessions-section">
            <h2>Your Past Sessions</h2>
            <?php 
                outputPastSessions($past_sessions);
                outputPager($page, $total_pages);
            ?>
        </section>
    </main>
    <?php outputFooter(); ?>
</body>
</html>
