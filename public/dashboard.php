<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/class_sessions.php';
require_once __DIR__ . '/../src/templates/past_sessions.php';
require_once __DIR__ . '/../src/templates/common.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$past_sessions = getUsersPastSessions($user_id, 10); // Get last 10 sessions

$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Fit-Pro</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
</head>
<body>
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
        <?php outputPastSessions($past_sessions) ?>
    </section>

    <br>
    <a href="logout.php">Logout</a>
</body>
</html>