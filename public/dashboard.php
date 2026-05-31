<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/class_sessions.php';
require_once __DIR__ . '/../src/models/users.php';
require_once __DIR__ . '/../src/templates/past_sessions.php';
require_once __DIR__ . '/../src/templates/pager.php';
require_once __DIR__ . '/../src/templates/common.php';
require_once __DIR__ . '/../src/templates/review_form.php';
require_once __DIR__ . '/../src/templates/class_session_card.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$today = "2026-06-02";

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$page_size = 5;
$offset = ($page - 1) * $page_size;

$user = getUserById($user_id);
$upcoming_sessions = getUsersUpcomingSessions($user_id, $today, 3);
$past_sessions = getUsersPastSessions($user_id, $today, $page_size, $offset);
$total_past_sessions = countUsersPastSessions($user_id, $today);
$total_pages = ceil($total_past_sessions / $page_size);

$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Fit-Pro</title>
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    <link href="css/templates/review_form.css" rel="stylesheet">
    <link href="css/templates/past_session.css" rel="stylesheet">
    <link href="css/templates/user_badge.css" rel="stylesheet">
    <link href="css/templates/session_card.css" rel="stylesheet">
    <link href="css/sessions.css" rel="stylesheet">
    <link href="css/modal.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <script src="js/modal.js" defer></script>
    <script src="js/sessions.js" defer></script>
</head>
<body>
    <?php outputHeader(); ?>
    <main>
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success" style="background: rgba(109, 191, 103, 0.1); border: 1px solid var(--success-color); color: var(--success-color); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center;">
                <?= htmlspecialchars($success === 'review_posted' ? 'Review posted successfully!' : $success) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" style="background: rgba(224, 122, 95, 0.1); border: 1px solid var(--danger-color); color: var(--danger-color); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <section class="upcoming-sessions-section">
            <h2>Your Upcoming Sessions</h2>
            <div id="sessions-container">
                <?php if (empty($upcoming_sessions)): ?>
                    <p>You have no upcoming sessions scheduled.</p>
                <?php else: ?>
                    <div class="card-container">
                        <?php foreach ($upcoming_sessions as $session): ?>
                            <?php outputClassSessionCard($session, $today); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="past-sessions-section">
            <h2>Your Past Sessions</h2>
            <?php 
                outputPastSessions($past_sessions);
                outputPager($page, $total_pages);
            ?>
        </section>
    </main>
    <?php outputFooter(); ?>

    <!-- Review Modal -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Write a Review</h2>
                <span class="close-modal" onclick="closeReviewModal()">&times;</span>
            </div>
            <div class="modal-body">
                <?php outputReviewForm(0, $user); ?>
            </div>
        </div>
    </div>
</body>
</html>
