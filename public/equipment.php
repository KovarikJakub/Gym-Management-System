<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php';


requireLogin();

$db = getDbConnection();
$isAdmin = $_SESSION['role'] === 'admin';
$error   = null;
$success = null;

// Admin POST actions
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name   = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? '';

        if (empty($name) || empty($status)) {
            $error = "Name and status are required.";
        } elseif (!in_array($status, ['available', 'in use', 'maintenance'])) {
            $error = "Invalid status.";
        } else {
            $stmt = $db->prepare("INSERT INTO equipment (name, status) VALUES (?, ?)");
            $stmt->execute([$name, $status]);
            $success = "Equipment '{$name}' added.";
        }
    }

    elseif ($action === 'update') {
        $id     = (int)($_POST['equipment_id'] ?? 0);
        $name   = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? '';

        if (!$id || empty($name) || empty($status)) {
            $error = "All fields are required.";
        } elseif (!in_array($status, ['available', 'in use', 'maintenance'])) {
            $error = "Invalid status.";
        } else {
            $stmt = $db->prepare("UPDATE equipment SET name = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $status, $id]);
            $success = "Equipment updated.";
        }
    }

    elseif ($action === 'delete') {
        $id = (int)($_POST['equipment_id'] ?? 0);
        if ($id) {
            $stmt = $db->prepare("DELETE FROM equipment WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Equipment deleted.";
        }
    }
}

//  Fetch all equipment 
$stmt = $db->query("SELECT * FROM equipment ORDER BY name");
$equipment = $stmt->fetchAll();

// Edit mode
$editItem = null;
if ($isAdmin && isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM equipment WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editItem = $stmt->fetch();
}

// Status counts
$counts = ['available' => 0, 'in use' => 0, 'maintenance' => 0];
foreach ($equipment as $item) {
    if (isset($counts[$item['status']])) $counts[$item['status']]++;
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
    <header>
        <div>
            <h1><a href="index.html">Fit-Pro</a></h1>
        </div>
        <nav id="menu">
            <input type="checkbox" id="hamburger">
            <label class="hamburger" for="hamburger"></label>
            <ul>
                <li><a href="gym_class_detail.html"><h2>Classes</h2></a></li>
                <li><a href="equipment.php"><h2>Equipment</h2></a></li>
                <li><a href="trainers.html"><h2>Trainers</h2></a></li>
                <li><a href="schedule.html"><h2>Schedule</h2></a></li>
            </ul>
        </nav>
        <div id="signup">
            <a href="logout.php"><h2>Logout</h2></a>
        </div>
    </header>

    <main>
        <section id="presentation">
            <div class="overlay">
                <h1>Equipment</h1>
                <p><?= $isAdmin ? 'Manage gym equipment' : 'Check equipment availability' ?></p>
            </div>
        </section>

        <!-- Status summary bar -->
        <section class="eq-summary">
            <div class="eq-stat">
                <span class="eq-stat__count"><?= $counts['available'] ?></span>
                <span class="eq-stat__label status-available">Available</span>
            </div>
            <div class="eq-stat">
                <span class="eq-stat__count"><?= $counts['in use'] ?></span>
                <span class="eq-stat__label status-inuse">In Use</span>
            </div>
            <div class="eq-stat">
                <span class="eq-stat__count"><?= $counts['maintenance'] ?></span>
                <span class="eq-stat__label status-maintenance">Maintenance</span>
            </div>
        </section>

        <section id="info">

            <?php if ($error || $success): ?>
            <p class="admin-notification <?= $error ? 'admin-notification--error' : 'admin-notification--success' ?>">
                <?= htmlspecialchars($error ?? $success) ?>
            </p>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
            <!-- Admin form -->
            <article class="boxes">
                <h2><?= $editItem ? 'Edit Equipment' : 'Add Equipment' ?></h2>
                <form method="post" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action"     value="<?= $editItem ? 'update' : 'create' ?>">
                    <?php if ($editItem): ?>
                        <input type="hidden" name="equipment_id" value="<?= (int)$editItem['id'] ?>">
                    <?php endif; ?>

                    <label class="admin-label">Name
                        <input class="admin-input" type="text" name="name"
                               value="<?= htmlspecialchars($editItem['name'] ?? '') ?>" required>
                    </label>

                    <label class="admin-label">Status
                        <select class="admin-input admin-select" name="status">
                            <?php foreach (['available', 'in use', 'maintenance'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($editItem['status'] ?? '') === $s ? 'selected' : '' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <div class="admin-form-actions">
                        <button type="submit" class="btn admin-submit-btn">
                            <?= $editItem ? 'Save Changes' : 'Add Equipment' ?>
                        </button>
                        <?php if ($editItem): ?>
                            <a href="equipment.php" class="admin-cancel-link">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </article>
            <?php endif; ?>

            <!-- Equipment list -->
            <article class="boxes admin-table-box <?= !$isAdmin ? 'eq-full-width' : '' ?>">
                <h2>All Equipment</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <?php if ($isAdmin): ?><th>Actions</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipment as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td>
                                <span class="eq-status eq-status--<?= str_replace(' ', '-', $item['status']) ?>">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </td>
                            <?php if ($isAdmin): ?>
                            <td class="admin-actions-cell">
                                <a href="equipment.php?edit=<?= $item['id'] ?>" class="admin-action-link">Edit</a>
                                <form method="post" style="display:inline;"
                                      onsubmit="return confirm('Delete <?= htmlspecialchars($item['name']) ?>?')">
                                    <input type="hidden" name="csrf_token"    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action"        value="delete">
                                    <input type="hidden" name="equipment_id"  value="<?= $item['id'] ?>">
                                    <button type="submit" class="admin-action-link admin-action-link--deactivate">Delete</button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($equipment)): ?>
                        <tr><td colspan="<?= $isAdmin ? 3 : 2 ?>" style="opacity:.5; text-align:center;">No equipment found.</td></tr>
                        <?php endif; ?>
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