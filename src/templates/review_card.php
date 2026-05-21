<?php

require_once __DIR__ . '/../Utils/helpers.php';

function getProfilePicPath($profile_photo)
{
    $uploadsBaseUrl = 'uploads/'; 
    return isNullOrEmptyString($profile_photo) ? 
        ($uploadsBaseUrl . "placeholder.jpg") : 
        ($uploadsBaseUrl . $profile_photo);
}

/**
 * Outputs a single review card.
 * 
 * @param array $review The review data.
 */
function outputReviewCard($review)
{
    $rating = (int)$review['rating'];
    ?>
<article class="review-card boxes">
    <div class="review-card-header">
        <span class="author-name"><?= htmlspecialchars($review['real_name'] ?: $review['username']) ?></span>
        <div class="review-rating">
            <?= str_repeat('⭐', $rating) ?>
        </div>
    </div>
    <img  src="<?= getProfilePicPath($review['profile_photo']) ?>" height="30" width="30"/>
        <time datetime="<?= date('Y-m-d', strtotime($review['posted_date'])) ?>">
        <?= date('M j, Y', strtotime($review['posted_date'])) ?>
    </time>
    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
</article>
<?php
}
