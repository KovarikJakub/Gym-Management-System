<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/equipment.php';

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
$validStatuses = ['available', 'in use', 'maintenance'];

if ($action === 'create') {
    $name   = trim($_POST['name'] ?? '');
    $status = $_POST['status'] ?? '';

    if (empty($name) || empty($status) || !in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid name or status.']);
        exit;
    }

    try {
        $newId = addEquipment($name, $status);
        echo json_encode([
            'success' => true,
            'message' => "Equipment '{$name}' added.",
            'data'    => ['id' => $newId, 'name' => $name, 'status' => $status],
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error adding equipment.']);
    }
    exit;
}

if ($action === 'update') {
    $id     = (int)($_POST['equipment_id'] ?? 0);
    $name   = trim($_POST['name'] ?? '');
    $status = $_POST['status'] ?? '';

    if (!$id || empty($name) || empty($status) || !in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        updateEquipment($id, $name, $status);
        echo json_encode([
            'success' => true,
            'message' => 'Equipment updated.',
            'data'    => ['id' => $id, 'name' => $name, 'status' => $status],
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating equipment.']);
    }
    exit;
}

if ($action === 'delete') {
    $id = (int)($_POST['equipment_id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }

    try {
        deleteEquipment($id);
        echo json_encode(['success' => true, 'message' => 'Equipment deleted.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting equipment.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);