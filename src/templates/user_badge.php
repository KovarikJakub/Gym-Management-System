<?php

/**
 * Outputs a small user badge with profile photo and clickable username.
 * 
 * @param array $user The user data (id, username, profile_photo, real_name).
 */
function outputUserBadge($user)
{
    $uploadsBaseUrl = 'uploads/';
    $photo = !empty($user['profile_photo']) ? $uploadsBaseUrl . $user['profile_photo'] : 'uploads/placeholder.jpg';
    $username = htmlspecialchars($user['username']);
    $userId = $user['id'];
    ?>
    <a href="view_profile.php?id=<?= $userId ?>" class="user-badge">
        <img src="<?= $photo ?>" alt="<?= $username ?>" class="user-badge-photo">
        <span class="user-badge-name"><?= $username ?></span>
    </a>
    <?php
}
