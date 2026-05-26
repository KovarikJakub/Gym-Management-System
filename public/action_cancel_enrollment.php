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

$db = getDbConnection();

try {
    $stmt = $db->prepare("DELETE FROM enrollments WHERE session_id = ? AND member_id = ?");
    $stmt->execute([$session_id, $user_id]);
    
    echo json_encode(['success' => true, 'message' => 'Enrollment successfully cancelled.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error while cancelling enrollment.']);
}