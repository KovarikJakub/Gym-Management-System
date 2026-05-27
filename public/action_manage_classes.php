<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/gym_classes.php';

requireLogin();

if ($_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

header('Content-Type: application/json');
if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'CSRF token validation failed.']);
    exit;
}

$action = $_POST['action'] ?? '';
$validTypes = ['yoga', 'pilates', 'cardio', 'workout', 'martial arts'];

if ($action === 'create') {
    $name            = trim($_POST['name'] ?? '');
    $type            = trim($_POST['type'] ?? '');
    $difficulty      = (int)($_POST['difficulty'] ?? 3);
    $defaultCapacity = (int)($_POST['default_capacity'] ?? 20);
    $description     = trim($_POST['description'] ?? '');

    if (empty($name) || empty($type) || !$defaultCapacity) {
        echo json_encode(['success' => false, 'message' => 'Name, type and capacity are required.']);
        exit;
    }
    if (!in_array($type, $validTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid class type.']);
        exit;
    }

    try {
        $newId = createGymClass($name, $difficulty, $description, $defaultCapacity, $type);
        echo json_encode([
            'success' => true,
            'message' => "Course '{$name}' successfully cataloged.",
            'data'    => ['id' => $newId, 'name' => $name, 'type' => $type, 'difficulty' => $difficulty, 'default_capacity' => $defaultCapacity, 'description' => $description]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error while saving course.']);
    }
    exit;
}

if ($action === 'update') {
    $id              = (int)($_POST['class_id'] ?? 0);
    $name            = trim($_POST['name'] ?? '');
    $type            = trim($_POST['type'] ?? '');
    $difficulty      = (int)($_POST['difficulty'] ?? 3);
    $defaultCapacity = (int)($_POST['default_capacity'] ?? 20);
    $description     = trim($_POST['description'] ?? '');

    if (!$id || empty($name) || empty($type) || !$defaultCapacity || !in_array($type, $validTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        updateGymClass($id, $name, $type, $difficulty, $defaultCapacity, $description);
        echo json_encode([
            'success' => true,
            'message' => 'Catalog item specifications updated.',
            'data'    => ['id' => $id, 'name' => $name, 'type' => $type, 'difficulty' => $difficulty, 'default_capacity' => $defaultCapacity, 'description' => $description]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to save catalog alterations.']);
    }
    exit;
}

if ($action === 'delete') {
    $id = (int)($_POST['class_id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid record identifier.']);
        exit;
    }

    try {
        deleteGymClass($id);
        echo json_encode(['success' => true, 'message' => 'Course removed from catalog logs.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Cannot drop class. Sessions are dependent on this item.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown processing method.']);