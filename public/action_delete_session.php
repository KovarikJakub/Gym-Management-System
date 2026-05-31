<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/class_sessions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'CSRF token validation failed.']);
        exit;
    }

    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
        exit;
    }

    $session_id = $_POST['session_id'] ?? null;

    if (!$session_id) {
        echo json_encode(['success' => false, 'message' => 'Session ID is required.']);
        exit;
    }

    try {
        $result = deleteSession($session_id);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Session successfully deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete session.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error while deleting the session.']);
    }
}
