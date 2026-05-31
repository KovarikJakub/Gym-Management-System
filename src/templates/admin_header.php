<?php

function outputAdminHeader($title, $subtitle) {
    ?>
    <section id="presentation">
        <div class="overlay">
            <h1><?= htmlspecialchars($title) ?></h1>
            <p><?= htmlspecialchars($subtitle) ?></p>
        </div>
    </section>
    <?php
}