<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/equipment.php'; 
require_once __DIR__ . '/../src/templates/common.php';
require_once __DIR__ . '/../src/templates/admin_toast.php';
require_once __DIR__ . '/../src/templates/admin_containers.php';

requireLogin();

$isAdmin = $_SESSION['role'] === 'admin';

$equipmentList = getAllEquipment();

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
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Fit-Pro - Equipment</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <link href="css/equipment.css" rel="stylesheet">
    <?php if ($isAdmin): ?>
        <script src="js/admin_equipment.js" defer></script>
    <?php endif; ?>
</head>
<body>
    <?php outputHeader(); ?>
    <main class="admin-wide-main">
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

        <section id="info" class="admin-management-layout">

            <?php if ($isAdmin): ?>
            <?php outputAdminBoxStart('Add New Equipment', 'equipmentFormModule'); ?>
                <form class="admin-form" id="equipmentLogisticsForm">
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
                    </div>
                </form>
            <?php outputAdminBoxEnd(); ?>
            <?php endif; ?>

            <?php outputAdminBoxStart('All Equipment', '', 'admin-table-box ' . (!$isAdmin ? 'admin-full-width' : '')); ?>
                <table class="admin-table" id="equipmentMasterTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="cell-centered">Status</th>
                            <?php if ($isAdmin): ?><th class="admin-actions-cell">Actions</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipmentList as $item): ?>
                        <tr id="row-equipment-<?= $item['id'] ?>">
                            <td class="cell-equipment-name"><?= htmlspecialchars($item['name']) ?></td>
                            <td class="cell-equipment-status cell-centered">
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

                        <tr id="emptyInventoryRow" <?= !empty($equipmentList) ? 'style="display:none;"' : '' ?>>
                            <td colspan="<?= $isAdmin ? 3 : 2 ?>" class="admin-table-empty">
                                No equipment found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php outputAdminBoxEnd(); ?>

        </section>
    </main>
    <?php outputFooter() ?>

    <?php outputAdminToast(); ?>
</body>
</html>
