<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/class_sessions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!verifyCsrf($_POST['csrf_token'] ?? ''))
        die("CSRF token validation failed.");

    $sessionId = $_POST['session_id'] ?? null;

    if ($sessionId === null)
    {
        echo json_encode(['success' => false, 'message' => 'No session_id provided.']);
        exit;
    }

    $userId = $_SESSION['user_id'];

    try {
        enrollUserToSession($userId, $sessionId);
        echo json_encode(['success' => true, 'message' => 'Enrolled successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Enrollment failed or already exists.']);
    }
}