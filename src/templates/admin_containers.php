<?php

function outputAdminBoxStart($title, $id = '', $extraClass = '') {
    $idAttr = !empty($id) ? 'id="' . htmlspecialchars($id) . '"' : '';
    ?>
    <article class="boxes <?= htmlspecialchars($extraClass) ?>" <?= $idAttr ?>>
        <h2><?= htmlspecialchars($title) ?></h2>
    <?php
}

function outputAdminBoxEnd() {
    ?>
    </article>
    <?php
}