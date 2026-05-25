<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php';
require_once __DIR__ . '/../src/templates/common.php';

requireLogin();

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$db = getDbConnection();

/* 
 * AJAX Endpoint Handler - processes requests and returns JSON data
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {

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

        if (empty($realName) || empty($username) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }
        if (!in_array($role, ['member', 'trainer'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid role.']);
            exit;
        }

        try {
            createUser($realName, $username, password_hash($password, PASSWORD_DEFAULT), $email, $role);
            $id = $db->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => "User '{$username}' created.",
                'user'    => ['id' => $id, 'real_name' => $realName, 'username' => $username, 'role' => $role, 'is_deactivated' => 0],
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

        if (!$id || empty($realName) || empty($username)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            exit;
        }

        $hashed = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

        try {
            updateUser($id, $realName, $username, $hashed);
            echo json_encode([
                'success' => true,
                'message' => 'User updated.',
                'user'    => ['id' => $id, 'real_name' => $realName, 'username' => $username],
            ]);
        } catch (PDOException $e) {
            $msg = str_contains($e->getMessage(), 'users.username') ? 'Username already exists.' : 'Could not update user.';
            echo json_encode(['success' => false, 'message' => $msg]);
        }
        exit;
    }

    if ($action === 'toggle_deactivate') {
        $id = (int)($_POST['user_id'] ?? 0);
        
        // Securely update the user status
        $stmtUpdate = $db->prepare("UPDATE users SET is_deactivated = NOT is_deactivated WHERE id = ? AND role != 'admin'");
        $stmtUpdate->execute([$id]);
        
        // Securely fetch updated status 
        $stmtCheck = $db->prepare("SELECT is_deactivated FROM users WHERE id = ?");
        $stmtCheck->execute([$id]);
        $row = $stmtCheck->fetch();
        
        echo json_encode([
            'success'        => true,
            'message'        => 'User status updated.',
            'is_deactivated' => (bool)$row['is_deactivated'],
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}

/* 
 * GET Request Handler 
 */
$stmt = $db->prepare("SELECT id, real_name, username, email, role, is_deactivated FROM users WHERE role != 'admin' ORDER BY role, real_name");
$stmt->execute();
$users = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fit-Pro – Admin Panel</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <?php outputHeader(); ?>
    <main>
        <section id="presentation">
            <div class="overlay">
                <h1>User Management</h1>
                <p>Fit-Pro Administration Dashboard</p>
            </div>
        </section>

        <section id="info">

            <!-- Form Component Container -->
            <article class="boxes" id="formBox">
                <h2 id="formTitle">Create New User</h2>

                <form class="admin-form" id="userForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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
                        <input class="admin-input" type="password" name="password" id="fieldPassword"
                               placeholder="Leave blank to keep current">
                    </label>

                    <div class="admin-form-actions">
                        <button type="submit" class="btn admin-submit-btn" id="formSubmitBtn">Create User</button>
                        <button type="button" class="admin-cancel-link" id="formCancelBtn" style="display:none; background:none; border:none; cursor:pointer;">Cancel</button>
                    </div>
                </form>
            </article>

            <!-- Data Table Container -->
            <article class="boxes admin-table-box">
                <h2>Members &amp; Trainers</h2>
                <table class="admin-table" id="usersTable">
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
                        <tr id="row-<?= $u['id'] ?>">
                            <td class="cell-name"><?= htmlspecialchars($u['real_name']) ?></td>
                            <td class="cell-username"><?= htmlspecialchars($u['username']) ?></td>
                            <td class="admin-role"><?= ucfirst(htmlspecialchars($u['role'])) ?></td>
                            <td>
                                <span class="admin-status <?= $u['is_deactivated'] ? 'admin-status--off' : 'admin-status--on' ?>">
                                    <?= $u['is_deactivated'] ? 'Deactivated' : 'Active' ?>
                                </span>
                            </td>
                            <td class="admin-actions-cell">
                                <button class="admin-action-link btn-edit"
                                        data-id="<?= $u['id'] ?>"
                                        data-name="<?= htmlspecialchars($u['real_name']) ?>"
                                        data-username="<?= htmlspecialchars($u['username']) ?>">
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
            </article>

        </section>
    </main>
    <?php outputFooter(); ?>
    <!-- Notification Toast System -->
    <div class="admin-toast" id="toast"></div>

    <script>
    (() => {
        const csrf       = document.querySelector('[name="csrf_token"]').value;
        const form       = document.getElementById('userForm');
        const formTitle  = document.getElementById('formTitle');
        const formAction = document.getElementById('formAction');
        const formUserId = document.getElementById('formUserId');
        const submitBtn  = document.getElementById('formSubmitBtn');
        const cancelBtn  = document.getElementById('formCancelBtn');
        const emailRow   = document.getElementById('emailRow');
        const roleRow    = document.getElementById('roleRow');
        const emailInput = document.getElementById('fieldEmail');
        const toast      = document.getElementById('toast');
        let toastTimer;

        // Display interface status messages
        function showToast(msg, isSuccess) {
            clearTimeout(toastTimer);
            toast.textContent = msg;
            toast.className   = `admin-toast admin-toast--${isSuccess ? 'success' : 'error'} show`;
            toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
        }

        // Wrapper utility for asynchronous POST requests
        async function sendPostRequest(data) {
            const body = new URLSearchParams({ csrf_token: csrf, ...data });
            const res  = await fetch(window.location.href, {
                method:  'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body,
            });
            return res.json();
        }

        // Restore form structure back to creation mode
        function resetForm() {
            formTitle.textContent       = 'Create New User';
            formAction.value            = 'create';
            formUserId.value            = '';
            submitBtn.textContent       = 'Create User';
            cancelBtn.style.display     = 'none';
            emailRow.style.display      = '';
            roleRow.style.display       = '';
            emailInput.setAttribute('required', 'required');
            document.getElementById('fieldPassword').placeholder = 'Leave blank to keep current';
            form.reset();
        }

        // Event delegation handler for the user data table
        document.getElementById('usersTable').addEventListener('click', async e => {

            // Handle edit trigger actions
            const editBtn = e.target.closest('.btn-edit');
            if (editBtn) {
                formTitle.textContent   = 'Edit User: ' + editBtn.dataset.username;
                formAction.value        = 'update';
                formUserId.value        = editBtn.dataset.id;
                submitBtn.textContent   = 'Save Changes';
                cancelBtn.style.display = '';
                emailRow.style.display  = 'none';   
                roleRow.style.display   = 'none';   
                emailInput.removeAttribute('required'); // Prevent HTML5 validation blocks on hidden fields
                
                document.getElementById('fieldRealName').value = editBtn.dataset.name;
                document.getElementById('fieldUsername').value = editBtn.dataset.username;
                document.getElementById('fieldPassword').value = '';
                document.getElementById('fieldPassword').placeholder = 'Leave blank to keep current';
                formBox.scrollIntoView({ behavior: 'smooth' });
                return;
            }

            // Handle account status toggle actions
            const toggleBtn = e.target.closest('.btn-toggle');
            if (toggleBtn) {
                const id  = toggleBtn.dataset.id;
                const res = await sendPostRequest({ action: 'toggle_deactivate', user_id: id });
                if (!res.success) { showToast(res.message, false); return; }

                const row        = document.getElementById(`row-${id}`);
                const badge      = row.querySelector('.admin-status');
                const isDeact    = res.is_deactivated;

                badge.textContent = isDeact ? 'Deactivated' : 'Active';
                badge.className   = `admin-status ${isDeact ? 'admin-status--off' : 'admin-status--on'}`;
                toggleBtn.textContent  = isDeact ? 'Reactivate' : 'Deactivate';
                toggleBtn.className    = `admin-action-link ${isDeact ? 'admin-action-link--activate' : 'admin-action-link--deactivate'} btn-toggle`;
                toggleBtn.dataset.deactivated = isDeact ? 1 : 0;

                showToast(res.message, true);
            }
        });

        cancelBtn.addEventListener('click', resetForm);

        // Async form submit management
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(form));
            const res  = await sendPostRequest(data);

            if (!res.success) { showToast(res.message, false); return; }

            showToast(res.message, true);

            if (data.action === 'create') {
                const u   = res.user;
                const row = document.createElement('tr');
                row.id    = `row-${u.id}`;
                row.innerHTML = `
                    <td class="cell-name">${sanitizeText(u.real_name)}</td>
                    <td class="cell-username">${sanitizeText(u.username)}</td>
                    <td class="admin-role">${capitalizeFirst(u.role)}</td>
                    <td><span class="admin-status admin-status--on">Active</span></td>
                    <td class="admin-actions-cell">
                        <button class="admin-action-link btn-edit"
                                data-id="${u.id}"
                                data-name="${sanitizeText(u.real_name)}"
                                data-username="${sanitizeText(u.username)}">Edit</button>
                        <button class="admin-action-link admin-action-link--deactivate btn-toggle"
                                data-id="${u.id}" data-deactivated="0">Deactivate</button>
                    </td>`;
                document.querySelector('#usersTable tbody').appendChild(row);
                resetForm();

            } else if (data.action === 'update') {
                const u   = res.user;
                const row = document.getElementById(`row-${u.id}`);
                row.querySelector('.cell-name').textContent     = u.real_name;
                row.querySelector('.cell-username').textContent = u.username;
                const editBtn = row.querySelector('.btn-edit');
                editBtn.dataset.name     = u.real_name;
                editBtn.dataset.username = u.username;
                resetForm();
            }
        });

        // Security sanitization and presentation helper functions
        function sanitizeText(str) {
            return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        
        function capitalizeFirst(str) { 
            return str.charAt(0).toUpperCase() + str.slice(1); 
        }

        const formBox = document.getElementById('formBox');
    })();
    </script>
</body>
</html>