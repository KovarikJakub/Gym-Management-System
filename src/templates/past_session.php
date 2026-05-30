<?php
require_once __DIR__ . '/user_badge.php';
require_once __DIR__ . '/../Utils/helpers.php';

function outputPastSession($session)
{
    $days_ago = formatTimeSinceDate($session['end_time']);
    $formatted_time = formatSessionTime($session['start_time'], $session['end_time']);
    
    $trainer = [
        'id' => $session['trainer_id'],
        'username' => $session['trainer_name'],
        'profile_photo' => $session['profile_photo'],
        'role' => $session['role']
    ];
    ?>
    <div class="past-session-card boxes">
        <div class="past-session-main">
            <div class="past-session-top">
                <?= $days_ago ?> days ago
            </div>
            
            <div class="past-session-content">
                <div class="session-info-group">
                    <h3><?= htmlspecialchars($session['class_name']) ?></h3>
                    <div class="session-meta">
                        <?php outputUserBadge($trainer); ?>
                        <div class="session-time-details">
                            <span class="past-session-time">🕒 <?= htmlspecialchars($formatted_time['time']) ?></span>
                            <span class="past-session-date">📅 <?= htmlspecialchars($formatted_time['date']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="past-session-footer">
            <div class="session-review-status">
                <?php if ($session['is_reviewed']): ?>
                    <span class="rating-stars">★ <?= htmlspecialchars($session['review_rating']) ?></span>
                    <?php 
                        $snippet = substr($session['review_comment'], 0, 40);
                        if (strlen($session['review_comment']) > 40) $snippet .= '...';
                    ?>
                    <span class="review-snippet">"<?= htmlspecialchars($snippet) ?>"</span>
                <?php else: ?>
                    <span class="no-review-text">Help us improve - <span class="write-review-link" onclick="openReviewModal(<?= $session['session_id'] ?>)">Write a review</span></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
