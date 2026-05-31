<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/class_sessions.php';
require_once __DIR__ . '/../src/models/users.php'; 

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

    $classId   = $_POST['class_id'] ?? null;
    $trainerId = $_POST['trainer_id'] ?? null;
    $startTime = $_POST['start_time'] ?? null;

    if (!$classId || !$trainerId || !$startTime) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    $startTimestamp = strtotime($startTime);
    $endTime = date('Y-m-d H:i:s', $startTimestamp + 3600); 
    $startTimeFormatted = date('Y-m-d H:i:s', $startTimestamp);

    try {
        $db = getDbConnection();
        $stmt = $db->prepare("INSERT INTO class_sessions (class_id, trainer_id, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$classId, $trainerId, $startTimeFormatted, $endTime]);

        echo json_encode(['success' => true, 'message' => 'Session successfully published to the schedule.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error while creating the session.']);
    }
}