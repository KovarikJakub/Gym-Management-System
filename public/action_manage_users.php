<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php';

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

if ($action === 'create') {
    $realName = trim($_POST['real_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'member';

    if (empty($realName) || empty($username) || empty($email) || empty($password) || !in_array($role, ['member', 'trainer'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        createUser($realName, $username, password_hash($password, PASSWORD_DEFAULT), $email, $role);
        $user = getUserByUsername($username); 
        echo json_encode([
            'success' => true,
            'message' => "User '{$username}' created.",
            'user'    => ['id' => $user['id'], 'real_name' => $realName, 'username' => $username, 'role' => $role, 'is_deactivated' => 0],
        ]);
    } catch (PDOException $e) {
        $msg = str_contains($e->getMessage(), 'users.username') ? 'Username already exists.'
             : (str_contains($e->getMessage(), 'users.email')    ? 'Email already exists.'
             : 'Could not create user.');
        echo json_encode(['success' => false, 'message' => $msg]);
    }
    exit;
}

if ($action === 'update') {
    $id       = (int)($_POST['user_id'] ?? 0);
    $realName = trim($_POST['real_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';

    if (!$id || empty($realName) || empty($username) || (!empty($role) && !in_array($role, ['member', 'trainer']))) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        exit;
    }

    $hashed = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

    try {
        updateUser($id, $realName, $username, $hashed);

        if (!empty($role)) {
            updateUserRole($id, $role);
        }

        $currentRole = getUserRole($id);

        echo json_encode([
            'success' => true,
            'message' => 'User updated.',
            'user'    => ['id' => $id, 'real_name' => $realName, 'username' => $username, 'role' => $currentRole],
        ]);
    } catch (PDOException $e) {
        $msg = str_contains($e->getMessage(), 'users.username') ? 'Username already exists.' : 'Could not update user.';
        echo json_encode(['success' => false, 'message' => $msg]);
    }
    exit;
}

if ($action === 'toggle_deactivate') {
    $id = (int)($_POST['user_id'] ?? 0);
    
    try {
        $isDeactivated = toggleUserStatus($id);
        echo json_encode([
            'success'        => true,
            'message'        => 'User status updated.',
            'is_deactivated' => $isDeactivated,
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating user status.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);