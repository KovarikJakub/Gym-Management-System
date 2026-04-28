<?php

require_once __DIR__ . '/../src/models/class_sessions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $sessionId = $_POST['session_id'] ?? null;
    $csrfToken = $_POST['csrf_token'] ?? '';

    if($sessionId === null)
    {
        echo json_encode(['success' => false, 'message' => 'No session_id provided.']);
        exit;
    }

    // TODO: Verify CSRF Token
    // TODO: after auth is working, current user_id will be used:
    // $userId = $_SESSION['user_id'];
    $userId = 1; // for now

    try {
        enrollUserToSession($userId, $sessionId);

        echo json_encode(['success' => true, 'message' => 'Enrolled successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Enrollment failed or already exists.']);
    }
}