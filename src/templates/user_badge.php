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
    $page_name = $user['role'] == "trainer" ? 
        "trainer_public_profile.php" : 
        "view_profile.php";
    ?>
    <a href="<?= $page_name ?>?id=<?= $userId ?>" class="user-badge">
        <img src="<?= $photo ?>" alt="<?= $username ?>" class="user-badge-photo">
        <span class="user-badge-name"><?= $username ?></span>
    </a>
    <?php
}
