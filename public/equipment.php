<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php';
require_once __DIR__ . '/../src/templates/common.php';

requireLogin();

$db      = getDbConnection();
$isAdmin = $_SESSION['role'] === 'admin';

/* --- AJAX endpoint --- */
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'CSRF token validation failed.']);
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name   = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? '';

        if (empty($name) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Name and status are required.']);
            exit;
        }
        if (!in_array($status, ['available', 'in use', 'maintenance'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status.']);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO equipment (name, status) VALUES (?, ?)");
        $stmt->execute([$name, $status]);
        $newId = $db->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => "Equipment '{$name}' added.",
            'data'    => ['id' => $newId, 'name' => $name, 'status' => $status],
        ]);
        exit;
    }

    if ($action === 'update') {
        $id     = (int)($_POST['equipment_id'] ?? 0);
        $name   = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? '';

        if (!$id || empty($name) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }
        if (!in_array($status, ['available', 'in use', 'maintenance'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status.']);
            exit;
        }

        $stmt = $db->prepare("UPDATE equipment SET name = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $status, $id]);

        echo json_encode([
            'success' => true,
            'message' => 'Equipment updated.',
            'data'    => ['id' => $id, 'name' => $name, 'status' => $status],
        ]);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['equipment_id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            exit;
        }

        $stmt = $db->prepare("DELETE FROM equipment WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Equipment deleted.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}

/*---GET---*/
$stmt          = $db->query("SELECT * FROM equipment ORDER BY name");
$equipmentList = $stmt->fetchAll();

$statusCounters = ['available' => 0, 'in use' => 0, 'maintenance' => 0];
foreach ($equipmentList as $item) {
    if (isset($statusCounters[$item['status']])) {
        $statusCounters[$item['status']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fit-Pro – Equipment</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <link href="css/equipment.css" rel="stylesheet">
</head>
<body>
    <?php outputHeader(); ?>
    <main>
        <!-- Status summary -->
        <section class="eq-summary">
            <div class="eq-stat">
                <span class="eq-stat__count" id="count-available"><?= $statusCounters['available'] ?></span>
                <span class="eq-stat__label status-available">Available</span>
            </div>
            <div class="eq-stat">
                <span class="eq-stat__count" id="count-in-use"><?= $statusCounters['in use'] ?></span>
                <span class="eq-stat__label status-inuse">In Use</span>
            </div>
            <div class="eq-stat">
                <span class="eq-stat__count" id="count-maintenance"><?= $statusCounters['maintenance'] ?></span>
                <span class="eq-stat__label status-maintenance">Maintenance</span>
            </div>
        </section>

        <section id="info">

            <?php if ($isAdmin): ?>
            <!-- Using admin.css classes -->
            <article class="boxes" id="equipmentFormModule">
                <h2 id="formTitleHeader">Add New Equipment</h2>
                <form class="admin-form" id="equipmentLogisticsForm">
                    <input type="hidden" name="csrf_token"   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="action"       value="create" id="formSubmissionAction">
                    <input type="hidden" name="equipment_id" value=""       id="formRecordId">

                    <label class="admin-label">Equipment Name
                        <input class="admin-input" type="text" name="name" id="inputFieldName" required>
                    </label>

                    <label class="admin-label">Status
                        <select class="admin-input admin-select" name="status" id="inputFieldStatus">
                            <option value="available">Available</option>
                            <option value="in use">In Use</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </label>

                    <div class="admin-form-actions">
                        <button type="submit" class="btn admin-submit-btn" id="formSubmitTrigger">Add Equipment</button>
                        <button type="button" class="admin-cancel-link" id="formCancelTrigger" style="display:none;">Cancel</button>
                    </div>
                </form>
            </article>
            <?php endif; ?>

            <!-- Table using admin.css classes -->
            <article class="boxes admin-table-box <?= !$isAdmin ? 'eq-full-width' : '' ?>">
                <h2>All Equipment</h2>
                <table class="admin-table" id="equipmentMasterTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <?php if ($isAdmin): ?><th>Actions</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipmentList as $item): ?>
                        <tr id="row-equipment-<?= $item['id'] ?>">
                            <td class="cell-equipment-name"><?= htmlspecialchars($item['name']) ?></td>
                            <td class="cell-equipment-status">
                                <span class="eq-status eq-status--<?= str_replace(' ', '-', $item['status']) ?>">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </td>
                            <?php if ($isAdmin): ?>
                            <td class="admin-actions-cell">
                                <button type="button" class="admin-action-link btn-action-edit"
                                        data-id="<?= $item['id'] ?>"
                                        data-name="<?= htmlspecialchars($item['name']) ?>"
                                        data-status="<?= htmlspecialchars($item['status']) ?>">
                                    Edit
                                </button>
                                <button type="button" class="admin-action-link admin-action-link--deactivate btn-action-delete"
                                        data-id="<?= $item['id'] ?>"
                                        data-name="<?= htmlspecialchars($item['name']) ?>">
                                    Delete
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>

                        <tr id="emptyInventoryRow" <?= !empty($equipmentList) ? 'style="display:none"' : '' ?>>
                            <td colspan="<?= $isAdmin ? 3 : 2 ?>" class="eq-table-empty">
                                No equipment found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </article>

        </section>
    </main>
    <?php outputFooter() ?>
    <!-- Toast using admin-notification colours -->
    <div id="interfaceAlertBox"></div>

    <?php if ($isAdmin): ?>
    <script>
    (() => {
        function esc(s) {
            return String(s)
                .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        const form        = document.getElementById('equipmentLogisticsForm');
        const tableBody   = document.querySelector('#equipmentMasterTable tbody');
        const actionField = document.getElementById('formSubmissionAction');
        const idField     = document.getElementById('formRecordId');
        const titleEl     = document.getElementById('formTitleHeader');
        const submitBtn   = document.getElementById('formSubmitTrigger');
        const cancelBtn   = document.getElementById('formCancelTrigger');
        const nameInput   = document.getElementById('inputFieldName');
        const statusInput = document.getElementById('inputFieldStatus');
        const toastEl     = document.getElementById('interfaceAlertBox');
        const csrf        = document.querySelector('[name="csrf_token"]').value;
        let toastTimer;

        /* --- Toast --- */
        function showToast(text, success) {
            clearTimeout(toastTimer);
            toastEl.textContent = text;
            toastEl.className   = `admin-notification admin-notification--${success ? 'success' : 'error'} toast-show`;
            toastTimer = setTimeout(() => { toastEl.className = ''; }, 3000);
        }

        /* --- AJAX helper --- */
        async function request(data) {
            const res = await fetch(window.location.href, {
                method:  'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body:    new URLSearchParams({ csrf_token: csrf, ...data }),
            });
            return res.json();
        }

        /* --- Reset form --- */
        function resetForm() {
            form.reset();
            actionField.value       = 'create';
            idField.value           = '';
            titleEl.textContent     = 'Add New Equipment';
            submitBtn.textContent   = 'Add Equipment';
            cancelBtn.style.display = 'none';
        }

        /* --- Update summary counters --- */
        function updateCounters() {
            const counts = { available: 0, inUse: 0, maintenance: 0 };
            tableBody.querySelectorAll('tr:not(#emptyInventoryRow)').forEach(row => {
                const b = row.querySelector('.eq-status');
                if (!b) return;
                if (b.classList.contains('eq-status--available'))   counts.available++;
                if (b.classList.contains('eq-status--in-use'))      counts.inUse++;
                if (b.classList.contains('eq-status--maintenance')) counts.maintenance++;
            });
            document.getElementById('count-available').textContent  = counts.available;
            document.getElementById('count-in-use').textContent     = counts.inUse;
            document.getElementById('count-maintenance').textContent = counts.maintenance;

            const empty = document.getElementById('emptyInventoryRow');
            empty.style.display = tableBody.querySelectorAll('tr:not(#emptyInventoryRow)').length === 0 ? '' : 'none';
        }

        /* --- Table clicks --- */
        tableBody.addEventListener('click', async e => {
            const editBtn   = e.target.closest('.btn-action-edit');
            const deleteBtn = e.target.closest('.btn-action-delete');

            if (editBtn) {
                actionField.value       = 'update';
                idField.value           = editBtn.dataset.id;
                nameInput.value         = editBtn.dataset.name;
                statusInput.value       = editBtn.dataset.status;
                titleEl.textContent     = `Edit: ${editBtn.dataset.name}`;
                submitBtn.textContent   = 'Save Changes';
                cancelBtn.style.display = 'inline-block';
                document.getElementById('equipmentFormModule').scrollIntoView({ behavior: 'smooth' });
                return;
            }

            if (deleteBtn) {
                if (!confirm(`Delete "${deleteBtn.dataset.name}"?`)) return;
                const result = await request({ action: 'delete', equipment_id: deleteBtn.dataset.id });
                showToast(result.message, result.success);
                if (result.success) {
                    document.getElementById(`row-equipment-${deleteBtn.dataset.id}`)?.remove();
                    updateCounters();
                    resetForm();
                }
            }
        });

        cancelBtn.addEventListener('click', resetForm);

        /* --- Submit --- */
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const data   = Object.fromEntries(new FormData(form));
            const result = await request(data);
            showToast(result.message, result.success);
            if (!result.success) return;

            const item        = result.data;
            const statusClass = item.status.replace(' ', '-');
            const statusLabel = item.status.charAt(0).toUpperCase() + item.status.slice(1);

            if (data.action === 'update') {
                const row = document.getElementById(`row-equipment-${item.id}`);
                row.querySelector('.cell-equipment-name').textContent = item.name;
                const badge = row.querySelector('.eq-status');
                badge.className   = `eq-status eq-status--${statusClass}`;
                badge.textContent = statusLabel;
                row.querySelector('.btn-action-edit').dataset.name   = item.name;
                row.querySelector('.btn-action-edit').dataset.status = item.status;
                row.querySelector('.btn-action-delete').dataset.name = item.name;
            } else {
                document.getElementById('emptyInventoryRow').insertAdjacentHTML('beforebegin', `
                    <tr id="row-equipment-${item.id}">
                        <td class="cell-equipment-name">${esc(item.name)}</td>
                        <td class="cell-equipment-status">
                            <span class="eq-status eq-status--${statusClass}">${statusLabel}</span>
                        </td>
                        <td class="admin-actions-cell">
                            <button type="button" class="admin-action-link btn-action-edit"
                                    data-id="${item.id}" data-name="${esc(item.name)}" data-status="${esc(item.status)}">Edit</button>
                            <button type="button" class="admin-action-link admin-action-link--deactivate btn-action-delete"
                                    data-id="${item.id}" data-name="${esc(item.name)}">Delete</button>
                        </td>
                    </tr>`);
            }

            updateCounters();
            resetForm();
        });
    })();
    </script>
    <?php endif; ?>
</body>
</html>