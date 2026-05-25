<?php

require_once __DIR__ . '/review_form.php';

function outputPastSessions($sessions)
{
    if (empty($sessions)) {
        echo "<p>You haven't attended any sessions yet.</p>";
        return;
    }

    foreach ($sessions as $session) {
        ?>
        <div class="past-session-card boxes" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd;">
            <h3><?= htmlspecialchars($session['class_name']) ?> (<?= htmlspecialchars($session['class_type']) ?>)</h3>
            <p><strong>Trainer:</strong> <?= htmlspecialchars($session['trainer_name']) ?></p>
            <p><strong>Time:</strong> <?= htmlspecialchars($session['start_time']) ?> - <?= htmlspecialchars($session['end_time']) ?></p>
            
            <?php if ($session['is_reviewed']): ?>
                <p style="color: green;"><strong>Status:</strong> Reviewed</p>
            <?php else: ?>
                <p><strong>Status:</strong> Not reviewed yet</p>
                <div class="review-section">
                    <h4>Add a Review</h4>
                    <?php outputReviewForm($session['session_id']); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
