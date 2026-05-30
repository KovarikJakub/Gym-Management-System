<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php'; 
require_once __DIR__ . '/../src/templates/common.php';
require_once __DIR__ . '/../src/templates/admin_header.php';
require_once __DIR__ . '/../src/templates/admin_containers.php';
require_once __DIR__ . '/../src/templates/admin_toast.php';

requireLogin();

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$users = getAllNonAdminUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Fit-Pro - Admin Panel</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <script src="js/admin_users.js" defer></script>
</head>
<body>
    <?php outputHeader(); ?>
    <main>
        <?php outputAdminHeader('User Management', 'Fit-Pro Administration Dashboard'); ?>

        <section id="info">
            <?php outputAdminBoxStart('Create New User', 'formBox'); ?>
                <form class="admin-form" id="userForm">
                    <input type="hidden" name="action"     value="create" id="formAction">
                    <input type="hidden" name="user_id"    value=""       id="formUserId">

                    <label class="admin-label">Full Name
                        <input class="admin-input" type="text" name="real_name" id="fieldRealName" required>
                    </label>
                    <label class="admin-label">Username
                        <input class="admin-input" type="text" name="username" id="fieldUsername" required>
                    </label>
                    <label class="admin-label" id="emailRow">Email
                        <input class="admin-input" type="email" name="email" id="fieldEmail" required>
                    </label>
                    <label class="admin-label" id="roleRow">Role
                        <select class="admin-input admin-select" name="role" id="fieldRole">
                            <option value="member">Member</option>
                            <option value="trainer">Trainer</option>
                        </select>
                    </label>
                    <label class="admin-label">Password
                        <input class="admin-input" type="password" name="password" id="fieldPassword" placeholder="Leave blank to keep current">
                    </label>

                    <div class="admin-form-actions">
                        <button type="submit" class="btn admin-submit-btn" id="formSubmitBtn">Create User</button>
                    </div>
                </form>
            <?php outputAdminBoxEnd(); ?>

            <?php outputAdminBoxStart('Members & Trainers', '', 'admin-table-box'); ?>
                <table class="admin-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="admin-actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr id="row-<?= $u['id'] ?>">
                            <td class="cell-name"><?= htmlspecialchars($u['real_name']) ?></td>
                            <td class="cell-username"><?= htmlspecialchars($u['username']) ?></td>
                            <td class="admin-role cell-role"><?= ucfirst(htmlspecialchars($u['role'])) ?></td>
                            <td>
                                <span class="admin-status <?= $u['is_deactivated'] ? 'admin-status--off' : 'admin-status--on' ?>">
                                    <?= $u['is_deactivated'] ? 'Deactivated' : 'Active' ?>
                                </span>
                            </td>
                            <td class="admin-actions-cell">
                                <button class="admin-action-link btn-edit"
                                        data-id="<?= $u['id'] ?>"
                                        data-name="<?= htmlspecialchars($u['real_name']) ?>"
                                        data-username="<?= htmlspecialchars($u['username']) ?>"
                                        data-role="<?= htmlspecialchars($u['role']) ?>">
                                    Edit
                                </button>
                                <button class="admin-action-link <?= $u['is_deactivated'] ? 'admin-action-link--activate' : 'admin-action-link--deactivate' ?> btn-toggle"
                                        data-id="<?= $u['id'] ?>"
                                        data-deactivated="<?= (int)$u['is_deactivated'] ?>">
                                    <?= $u['is_deactivated'] ? 'Reactivate' : 'Deactivate' ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php outputAdminBoxEnd(); ?>
        </section>
    </main>
    
    <?php outputFooter(); ?>
    <?php outputAdminToast('toast', 'admin-toast'); ?>
</body>
</html>