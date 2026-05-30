<?php

function outputAdminToast($id = 'interfaceAlertBox', $className = 'admin-notification') {
    ?>
    <div id="<?= htmlspecialchars($id) ?>" class="<?= htmlspecialchars($className) ?>"></div>
    <?php
}