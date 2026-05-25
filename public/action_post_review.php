<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/reviews.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_id = $_POST['session_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $comment = $_POST['comment'] ?? '';
    $user_id = $_SESSION['user_id'];

    if ($session_id && $rating) {
        try {
            addReview($user_id, $session_id, $rating, $comment);
            header("Location: dashboard.php?success=review_posted");
            exit;
        } catch (Exception $e) {
            header("Location: dashboard.php?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
}

header("Location: dashboard.php");
exit;
