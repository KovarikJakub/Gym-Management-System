<?php

require_once __DIR__ . '/user_badge.php';

/**
 * Outputs a single review card.
 * 
 * @param array $review The review data.
 */
function outputReviewCard($review)
{
    $rating = (int)$review['rating'];
    $user = [
        'id' => $review['author_id'],
        'username' => $review['username'],
        'profile_photo' => $review['profile_photo'],
        'role' => 'member'
    ];
    ?>
<article class="review-card boxes">
    <div class="review-card-header">
        <?php outputUserBadge($user); ?>
        <div class="review-rating">
            <?= str_repeat('⭐', $rating) ?>
        </div>
    </div>
    
    <time datetime="<?= date('Y-m-d', strtotime($review['posted_date'])) ?>">
        <?= date('M j, Y', strtotime($review['posted_date'])) ?>
    </time>
    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
</article>
<?php
}
