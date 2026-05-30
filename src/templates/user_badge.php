<?php

/**
 * Outputs a small user badge with profile photo and username.
 * 
 * @param array $user The user data (id, username, profile_photo, real_name).
 */
function outputUserBadge($user)
{
    $uploadsBaseUrl = 'uploads/';
    $photo = !empty($user['profile_photo']) ? $uploadsBaseUrl . $user['profile_photo'] : 'uploads/placeholder.jpg';
    $username = htmlspecialchars($user['username']);
    ?>
    <span class="user-badge">
        <img src="<?= $photo ?>" alt="<?= $username ?>" class="user-badge-photo">
        <span class="user-badge-name"><?= $username ?></span>
    </span>
    <?php
}
