<?php

/**
 * Outputs the review input form.
 * 
 * @param int $gym_class_id The ID of the gym class being reviewed.
 */
function outputReviewForm($gym_class_id)
{
    // In a real application, we would check if the user is logged in here
    // or pass the current user's information as a parameter.
    $username = $_SESSION['username'] ?? '{Current user}';
    ?>
    <form class="review-form boxes">
        <div class="current-user-badge">
            <span class="user-label">Posting as:</span>
            <span class="author-name"><?= htmlspecialchars($username) ?></span>
        </div>
        <div class="rating-selector">
            <label for="rating">Score:</label>
            <select name="rating" id="rating">
                <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                <option value="4">⭐⭐⭐⭐ (Good)</option>
                <option value="3">⭐⭐⭐ (Average)</option>
                <option value="2">⭐⭐ (Poor)</option>
                <option value="1">⭐ (Terrible)</option>
            </select>
        </div>
        <div class="review-form-input-container">
            <input type="text" id="comment" name="comment" placeholder="Let others know what you think..." required />
            <button type="submit" class="btn">Post</button>
        </div>
    </form>
    <?php
}
