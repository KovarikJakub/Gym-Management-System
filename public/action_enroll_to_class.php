<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php'; 

requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'CSRF validation failed.']);
    exit;
}

$session_id = (int)($_POST['session_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if (!$session_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid session ID.']);
    exit;
}

$db = getDbConnection();

try {
    $stmt = $db->prepare("INSERT INTO enrollments (member_id, session_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $session_id]);
    
    echo json_encode(['success' => true, 'message' => 'Successfully enrolled in the class!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Cannot enroll (you are already enrolled or an error occurred).']);
}