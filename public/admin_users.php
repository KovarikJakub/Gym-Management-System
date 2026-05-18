<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php';

requireLogin();

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$db = getDbConnection();
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $realName = trim($_POST['real_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'member';

        if (empty($realName) || empty($username) || empty($email) || empty($password)) {
            $error = "All fields are required.";
        } elseif (!in_array($role, ['member', 'trainer'])) {
            $error = "Invalid role.";
        } else {
            try {
                createUser($realName, $username, password_hash($password, PASSWORD_DEFAULT), $email, $role);
                $success = "User '{$username}' created.";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'users.username') !== false)
                    $error = "Username already exists.";
                elseif (strpos($e->getMessage(), 'users.email') !== false)
                    $error = "Email already exists.";
                else
                    $error = "Could not create user.";
            }
        }
    }

    elseif ($action === 'update') {
        $id       = (int)($_POST['user_id'] ?? 0);
        $realName = trim($_POST['real_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$id || empty($realName) || empty($username)) {
            $error = "Missing required fields.";
        } else {
            $hashed = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
            try {
                updateUser($id, $realName, $username, $hashed);
                $success = "User updated.";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'users.username') !== false)
                    $error = "Username already exists.";
                else
                    $error = "Could not update user.";
            }
        }
    }

    elseif ($action === 'toggle_deactivate') {
        $id = (int)($_POST['user_id'] ?? 0);
        $stmt = $db->prepare("UPDATE users SET is_deactivated = NOT is_deactivated WHERE id = ? AND role != 'admin'");
        $stmt->execute([$id]);
        $success = "User status updated.";
    }
}

$stmt = $db->prepare("SELECT id, real_name, username, email, role, is_deactivated FROM users WHERE role != 'admin' ORDER BY role, real_name");
$stmt->execute();
$users = $stmt->fetchAll();

$editUser = null;
if (isset($_GET['edit'])) {
    $editUser = getUserById((int)$_GET['edit']);
    if ($editUser && $editUser['role'] === 'admin') $editUser = null;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fit-Pro &ndash; Admin Panel</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <header>
        <div>
            <h1><a href="index.html">Fit-Pro</a></h1>
        </div>
        <nav id="menu">
            <input type="checkbox" id="hamburger">
            <label class="hamburger" for="hamburger"></label>
            <ul>
                <li><a href="gym_class_detail.html"><h2>Classes</h2></a></li>
                <li><a href="equipment.html"><h2>Equipment</h2></a></li>
                <li><a href="trainers.html"><h2>Trainers</h2></a></li>
                <li><a href="schedule.html"><h2>Schedule</h2></a></li>
            </ul>
        </nav>
        <div id="signup">
            <a href="logout.php"><h2>Logout</h2></a>
        </div>
    </header>


?>

<main>
    <section id="presentation">
        <div class="overlay">
            <h1>User Management</h1>
            <p>Fit-Pro Administration Dashboard</p>
        </div>
    </section>

    <section id="info">

        <?php if ($error || $success): ?>
        <p class="admin-notification <?= $error ? 'admin-notification--error' : 'admin-notification--success' ?>">
            <?= htmlspecialchars($error ?? $success) ?>
        </p>
        <?php endif; ?>

        <!-- Form box -->
        <article class="boxes">
            <?php if ($editUser): ?>
                <h2>Edit User: <?= htmlspecialchars($editUser['username']) ?></h2>
                <form method="post" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?= (int)$editUser['id'] ?>">

                    <label class="admin-label">Full Name
                        <input class="admin-input" type="text" name="real_name" value="<?= htmlspecialchars($editUser['real_name']) ?>" required>
                    </label>
                    <label class="admin-label">Username
                        <input class="admin-input" type="text" name="username" value="<?= htmlspecialchars($editUser['username']) ?>" required>
                    </label>
                    <label class="admin-label">New Password
                        <input class="admin-input" type="password" name="password" placeholder="Leave blank to keep current">
                    </label>
                    <div class="admin-form-actions">
                        <button type="submit" class="btn admin-submit-btn">Save Changes</button>
                        <a href="admin_users.php" class="admin-cancel-link">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <h2>Create New User</h2>
                <form method="post" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="create">

                    <label class="admin-label">Full Name
                        <input class="admin-input" type="text" name="real_name" required>
                    </label>
                    <label class="admin-label">Username
                        <input class="admin-input" type="text" name="username" required>
                    </label>
                    <label class="admin-label">Email
                        <input class="admin-input" type="email" name="email" required>
                    </label>
                    <label class="admin-label">Password
                        <input class="admin-input" type="password" name="password" required>
                    </label>
                    <label class="admin-label">Role
                        <select class="admin-input admin-select" name="role">
                            <option value="member">Member</option>
                            <option value="trainer">Trainer</option>
                        </select>
                    </label>
                    <div class="admin-form-actions">
                        <button type="submit" class="btn admin-submit-btn">Create User</button>
                    </div>
                </form>
            <?php endif; ?>
        </article>

        <!-- Table box -->
        <article class="boxes admin-table-box">
            <h2>Members &amp; Trainers</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['real_name']) ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td class="admin-role"><?= htmlspecialchars($u['role']) ?></td>
                        <td>
                            <?php if ($u['is_deactivated']): ?>
                                <span class="admin-status admin-status--off">Deactivated</span>
                            <?php else: ?>
                                <span class="admin-status admin-status--on">Active</span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-actions-cell">
                            <a href="admin_users.php?edit=<?= $u['id'] ?>" class="admin-action-link">Edit</a>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="toggle_deactivate">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="admin-action-link admin-action-link--<?= $u['is_deactivated'] ? 'activate' : 'deactivate' ?>">
                                    <?= $u['is_deactivated'] ? 'Reactivate' : 'Deactivate' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </article>

    </section>
</main>

    <footer>
        <p>&copy; 2026 GymFit Studio. All rights reserved.</p>
        <nav class="footer-links">
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
        </nav>
    </footer>
</body>
</html>