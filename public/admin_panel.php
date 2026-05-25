<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php';

requireLogin();

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$db = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'CSRF token validation failed.']);
        exit;
    }

    $action = $_POST['action'] ?? '';

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
        if (!in_array($type, ['yoga', 'pilates', 'cardio', 'workout', 'martial arts'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid structural type choice.']);
            exit;
        }

        try {
            $stmt = $db->prepare("INSERT INTO gym_classes (name, difficulty, description, default_capacity, type) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $difficulty, $description, $defaultCapacity, $type]);
            $newId = $db->lastInsertId();

            echo json_encode([
                'success' => true,
                'message' => "Course '{$name}' successfully cataloged.",
                'data'    => [
                    'id'               => $newId,
                    'name'             => $name,
                    'type'             => $type,
                    'difficulty'       => $difficulty,
                    'default_capacity' => $defaultCapacity,
                    'description'      => $description
                ],
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

        if (!$id || empty($name) || empty($type) || !$defaultCapacity) {
            echo json_encode(['success' => false, 'message' => 'All mandatory parameters are required.']);
            exit;
        }

        try {
            $stmt = $db->prepare("UPDATE gym_classes SET name = ?, type = ?, difficulty = ?, default_capacity = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $type, $difficulty, $defaultCapacity, $description, $id]);

            echo json_encode([
                'success' => true,
                'message' => 'Catalog item specifications updated.',
                'data'    => [
                    'id'               => $id,
                    'name'             => $name,
                    'type'             => $type,
                    'difficulty'       => $difficulty,
                    'default_capacity' => $defaultCapacity,
                    'description'      => $description
                ],
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
            $stmt = $db->prepare("DELETE FROM gym_classes WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Course removed from catalog logs.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Cannot drop class. Active scheduled sessions are currently dependent on this item.']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown processing method.']);
    exit;
}

$classesList = $db->query("SELECT * FROM gym_classes ORDER BY name")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fit-Pro - Class Catalog</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <link href="css/equipment.css" rel="stylesheet">
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
                <li><a href="admin_classes.php"><h2>Classes</h2></a></li>
                <li><a href="equipment.php"><h2>Equipment</h2></a></li>
                <li><a href="schedule.php"><h2>Schedule</h2></a></li>
            </ul>
        </nav>
        <div id="signup">
            <a href="logout.php"><h2>Logout</h2></a>
        </div>
    </header>

    <main>
        <section id="presentation">
            <div class="overlay">
                <h1>Class Catalog</h1>
                <p>Manage courses, difficulty coefficients, and curriculum configurations</p>
            </div>
        </section>

        <section id="info">
            
            <div id="interfaceAlertBox" class="admin-notification"></div>

            <article class="boxes" id="equipmentFormModule">
                <h2 id="formTitleHeader">Add New Course</h2>
                <form class="admin-form" id="equipmentLogisticsForm">
                    <input type="hidden" name="csrf_token"   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="action"       value="create" id="formSubmissionAction">
                    <input type="hidden" name="class_id"     value=""       id="formRecordId">

                    <label class="admin-label">
                        <span>Course Title</span>
                        <input class="admin-input" type="text" name="name" id="inputFieldName" required>
                    </label>

                    <label class="admin-label">
                        <span>Discipline Type</span>
                        <select class="admin-input admin-select" name="type" id="inputFieldType">
                            <option value="yoga">Yoga</option>
                            <option value="pilates">Pilates</option>
                            <option value="cardio">Cardio</option>
                            <option value="workout">Workout</option>
                            <option value="martial arts">Martial Arts</option>
                        </select>
                    </label>

                    <label class="admin-label">
                        <span>Difficulty Tier (1-5)</span>
                        <select class="admin-input admin-select" name="difficulty" id="inputFieldDifficulty">
                            <option value="1">1 - Very Easy</option>
                            <option value="2">2 - Easy</option>
                            <option value="3" selected>3 - Moderate</option>
                            <option value="4">4 - Advanced</option>
                            <option value="5">5 - Intense</option>
                        </select>
                    </label>

                    <label class="admin-label">
                        <span>Default Room Capacity</span>
                        <input class="admin-input" type="number" name="default_capacity" id="inputFieldCapacity" min="5" max="50" value="20" required>
                    </label>

                    <label class="admin-label">
                        <span>Course Description</span>
                        <input class="admin-input" type="text" name="description" id="inputFieldDescription" placeholder="Brief summary of syllabus goals...">
                    </label>

                    <div class="admin-form-actions">
                        <button type="submit" class="btn admin-submit-btn" id="formSubmitTrigger">Save Course</button>
                        <button type="button" class="admin-cancel-link" id="formCancelTrigger" style="display:none; background:none; border:none; cursor:pointer;">Cancel Edit</button>
                    </div>
                </form>
            </article>

            <article class="boxes admin-table-box">
                <h2>Active Curriculum Catalog</h2>
                <table class="admin-table" id="equipmentMasterTable">
                    <thead>
                        <tr>
                            <th>Course Details</th>
                            <th style="text-align: center;">Discipline</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classesList as $item): ?>
                        <tr id="row-catalog-<?= $item['id'] ?>">
                            <td class="cell-equipment-name">
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                <div style="font-size:0.78rem; opacity:0.6; margin-top:0.2rem;">
                                    Difficulty: <span class="diff-val"><?= (int)$item['difficulty'] ?></span>/5 | Capacity: <span class="capacity-val"><?= (int)$item['default_capacity'] ?></span>
                                </div>
                                <div class="desc-val" style="font-size:0.75rem; opacity:0.5; font-style:italic; margin-top:0.15rem; <?= empty($item['description']) ? 'display:none;' : '' ?>"><?= htmlspecialchars($item['description'] ?? '') ?></div>
                            </td>
                            <td class="cell-equipment-status">
                                <span class="eq-status eq-status--in-use">
                                    <?= htmlspecialchars($item['type']) ?>
                                </span>
                            </td>
                            <td style="text-align: right;" class="admin-actions-cell">
                                <button type="button" class="admin-action-link btn-action-edit"
                                        data-id="<?= $item['id'] ?>"
                                        data-name="<?= htmlspecialchars($item['name']) ?>"
                                        data-type="<?= htmlspecialchars($item['type']) ?>"
                                        data-difficulty="<?= (int)$item['difficulty'] ?>"
                                        data-capacity="<?= (int)$item['default_capacity'] ?>"
                                        data-description="<?= htmlspecialchars($item['description'] ?? '') ?>">
                                    Edit
                                </button>
                                <button type="button" class="admin-action-link admin-action-link--deactivate btn-action-delete"
                                        style="margin-left: 1rem;"
                                        data-id="<?= $item['id'] ?>"
                                        data-name="<?= htmlspecialchars($item['name']) ?>">
                                    Remove
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <tr id="emptyInventoryRow" style="<?= empty($classesList) ? '' : 'display:none;' ?>">
                            <td colspan="3" class="eq-table-empty">No active courses cataloged in the curriculum logs.</td>
                        </tr>
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

    <script>
    (() => {
        const form            = document.getElementById('equipmentLogisticsForm');
        const tableBody       = document.querySelector('#equipmentMasterTable tbody');
        const actionField     = document.getElementById('formSubmissionAction');
        const idField         = document.getElementById('formRecordId');
        const titleEl         = document.getElementById('formTitleHeader');
        const submitBtn       = document.getElementById('formSubmitTrigger');
        const cancelBtn       = document.getElementById('formCancelTrigger');
        
        const nameInput       = document.getElementById('inputFieldName');
        const typeInput       = document.getElementById('inputFieldType');
        const difficultyInput = document.getElementById('inputFieldDifficulty');
        const capacityInput   = document.getElementById('inputFieldCapacity');
        const descInput       = document.getElementById('inputFieldDescription');
        
        const alertBox        = document.getElementById('interfaceAlertBox');
        const csrf            = document.querySelector('[name="csrf_token"]').value;
        let toastTimer;

        function showMessage(text, success) {
            clearTimeout(toastTimer);
            alertBox.textContent = text;
            alertBox.className = `admin-notification admin-notification--${success ? 'success' : 'error'} toast-show`;
            toastTimer = setTimeout(() => { alertBox.className = 'admin-notification'; }, 3500);
        }

        async function request(data) {
            const response = await fetch(window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new URLSearchParams({ csrf_token: csrf, ...data })
            });
            return response.json();
        }

        function resetForm() {
            form.reset();
            actionField.value = 'create';
            idField.value = '';
            titleEl.textContent = 'Add New Course';
            submitBtn.textContent = 'Save Course';
            cancelBtn.style.display = 'none';
        }

        function toggleEmptyNotice() {
            const actualRows = tableBody.querySelectorAll('tr:not(#emptyInventoryRow)');
            document.getElementById('emptyInventoryRow').style.display = actualRows.length === 0 ? '' : 'none';
        }

        tableBody.addEventListener('click', async e => {
            const editBtn   = e.target.closest('.btn-action-edit');
            const deleteBtn = e.target.closest('.btn-action-delete');

            if (editBtn) {
                actionField.value       = 'update';
                idField.value           = editBtn.dataset.id;
                nameInput.value         = editBtn.dataset.name;
                typeInput.value         = editBtn.dataset.type;
                difficultyInput.value   = editBtn.dataset.difficulty;
                capacityInput.value     = editBtn.dataset.capacity;
                descInput.value         = editBtn.dataset.description;

                titleEl.textContent   = `Modify Curriculum: ${editBtn.dataset.name}`;
                submitBtn.textContent = 'Save Changes';
                cancelBtn.style.display = 'inline-block';
                document.getElementById('equipmentFormModule').scrollIntoView({ behavior: 'smooth' });
                return;
            }

            if (deleteBtn) {
                if (!confirm(`Permanently drop "${deleteBtn.dataset.name}" from catalog?`)) return;

                const result = await request({ action: 'delete', class_id: deleteBtn.dataset.id });
                showMessage(result.message, result.success);

                if (result.success) {
                    document.getElementById(`row-catalog-${deleteBtn.dataset.id}`)?.remove();
                    toggleEmptyNotice();
                    resetForm();
                }
            }
        });

        cancelBtn.addEventListener('click', resetForm);

        form.addEventListener('submit', async e => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(form));
            const result = await request(data);

            showMessage(result.message, result.success);
            if (!result.success) return;

            const item = result.data;

            if (data.action === 'update') {
                const row = document.getElementById(`row-catalog-${item.id}`);
                row.querySelector('.cell-equipment-name strong').textContent = item.name;
                row.querySelector('.cell-equipment-name .diff-val').textContent = item.difficulty;
                row.querySelector('.cell-equipment-name .capacity-val').textContent = item.default_capacity;
                
                const descDiv = row.querySelector('.desc-val');
                if (item.description) {
                    descDiv.textContent = item.description;
                    descDiv.style.display = 'block';
                } else {
                    descDiv.style.display = 'none';
                }
                
                row.querySelector('.cell-equipment-status .eq-status').textContent = item.type;

                const editBtnRef = row.querySelector('.btn-action-edit');
                editBtnRef.dataset.name = item.name;
                editBtnRef.dataset.type = item.type;
                editBtnRef.dataset.difficulty = item.difficulty;
                editBtnRef.dataset.capacity = item.default_capacity;
                editBtnRef.dataset.description = item.description;
                
                row.querySelector('.btn-action-delete').dataset.name = item.name;
            } else {
                const descDisplay = item.description ? 'block' : 'none';
                
                tableBody.insertAdjacentHTML('beforeend', `
                    <tr id="row-catalog-${item.id}">
                        <td class="cell-equipment-name">
                            <strong>${esc(item.name)}</strong>
                            <div style="font-size:0.78rem; opacity:0.6; margin-top:0.2rem;">
                                Difficulty: <span class="diff-val">${item.difficulty}</span>/5 | Capacity: <span class="capacity-val">${item.default_capacity}</span>
                            </div>
                            <div class="desc-val" style="font-size:0.75rem; opacity:0.5; font-style:italic; margin-top:0.15rem; display:${descDisplay};">${esc(item.description)}</div>
                        </td>
                        <td class="cell-equipment-status">
                            <span class="eq-status eq-status--in-use">
                                ${esc(item.type)}
                            </span>
                        </td>
                        <td style="text-align: right;" class="admin-actions-cell">
                            <button type="button" class="admin-action-link btn-action-edit"
                                    data-id="${item.id}"
                                    data-name="${esc(item.name)}"
                                    data-type="${esc(item.type)}"
                                    data-difficulty="${item.difficulty}"
                                    data-capacity="${item.default_capacity}"
                                    data-description="${esc(item.description)}">Edit</button>
                            <button type="button" class="admin-action-link admin-action-link--deactivate btn-action-delete"
                                    style="margin-left: 1rem;"
                                    data-id="${item.id}"
                                    data-name="${esc(item.name)}">Remove</button>
                        </td>
                    </tr>
                `);
            }
            toggleEmptyNotice();
            resetForm();
        });

        function esc(str) {
            return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
    })();
    </script>
</body>
</html>