<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/gym_classes.php'; 
require_once __DIR__ . '/../src/templates/common.php';
require_once __DIR__ . '/../src/templates/admin_header.php';
require_once __DIR__ . '/../src/templates/admin_toast.php';
require_once __DIR__ . '/../src/templates/admin_containers.php';

requireLogin();

$isAdmin = $_SESSION['role'] === 'admin';

$classesList = getAllGymClasses();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Fit-Pro - Class Catalog</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <link href="css/equipment.css" rel="stylesheet">
    
    <?php if ($isAdmin): ?>
        <script src="js/admin_classes.js" defer></script>
    <?php endif; ?>
</head>
<body>
    <?php outputHeader(); ?>

    <main>
        <?php outputAdminHeader('Class Catalog', 'Browse our curriculum and difficulty levels'); ?>

        <section id="info">
            <?php outputAdminToast('interfaceAlertBox', 'admin-notification'); ?>

            <?php if ($isAdmin): ?>
                <?php outputAdminBoxStart('Add New Course', 'classFormBox'); ?>
                    <form class="admin-form" id="classForm">
                        <input type="hidden" name="action" value="create" id="formSubmissionAction">
                        <input type="hidden" name="class_id" value="" id="formRecordId">

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
                        </div>
                    </form>
                <?php outputAdminBoxEnd(); ?>
            <?php endif; ?>

            <?php outputAdminBoxStart('Active Curriculum Catalog', '', 'admin-table-box ' . (!$isAdmin ? 'eq-full-width' : '')); ?>
                <table class="admin-table" id="classesTable">
                    <thead>
                        <tr>
                            <th>Course Details</th>
                            <th class="cell-centered">Discipline</th>
                            <?php if ($isAdmin): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classesList as $item): ?>
                        <tr id="row-catalog-<?= $item['id'] ?>">
                            <td class="cell-class-name">
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                <div class="class-stats">
                                    Difficulty: <span class="diff-val"><?= (int)$item['difficulty'] ?></span>/5 | Capacity: <span class="capacity-val"><?= (int)$item['default_capacity'] ?></span>
                                </div>
                                <div class="desc-val" style="<?= empty($item['description']) ? 'display:none;' : '' ?>">
                                    <?= htmlspecialchars($item['description'] ?? '') ?>
                                </div>
                            </td>
                            <td class="cell-class-status cell-centered">
                                <span class="admin-status admin-status--on text-capitalize">
                                    <?= htmlspecialchars($item['type']) ?>
                                </span>
                            </td>
                            
                            <?php if ($isAdmin): ?>
                            <td class="admin-actions-cell">
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
                                        data-id="<?= $item['id'] ?>"
                                        data-name="<?= htmlspecialchars($item['name']) ?>">
                                    Remove
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        
                        <tr id="emptyInventoryRow" style="<?= empty($classesList) ? '' : 'display:none;' ?>">
                            <td colspan="<?= $isAdmin ? 3 : 2 ?>" class="eq-table-empty">No active courses cataloged in the curriculum logs.</td>
                        </tr>
                    </tbody>
                </table>
            <?php outputAdminBoxEnd(); ?>
        </section>
    </main>

    <?php outputFooter(); ?>
</body>
</html>
