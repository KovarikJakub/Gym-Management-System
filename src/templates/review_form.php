<?php

/**
 * Outputs the review input form for a specific session.
 * 
 * @param int $session_id The ID of the session being reviewed.
 */
function outputReviewForm($session_id)
{
    $username = $_SESSION['username'] ?? '{Current user}';
    ?>
    <form action="action_post_review.php" method="POST" class="review-form boxes">
        <input type="hidden" name="session_id" value="<?= htmlspecialchars($session_id) ?>">
        
        <div class="current-user-badge">
            <span class="user-label">Posting as:</span>
            <span class="author-name"><?= htmlspecialchars($username) ?></span>
        </div>

        <div class="rating-selector">
            <label for="rating_<?= $session_id ?>">Score:</label>
            <select name="rating" id="rating_<?= $session_id ?>">
                <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                <option value="4">⭐⭐⭐⭐ (Good)</option>
                <option value="3">⭐⭐⭐ (Average)</option>
                <option value="2">⭐⭐ (Poor)</option>
                <option value="1">⭐ (Terrible)</option>
            </select>
        </div>

        <div class="review-form-input-container">
            <textarea name="comment" id="comment_<?= $session_id ?>" placeholder="Let others know what you think..." required style="width: 100%; height: 60px; margin-top: 10px;"></textarea>
            <button type="submit" class="btn" style="display: block; margin-top: 10px;">Post Review</button>
        </div>
    </form>
    <?php
}
