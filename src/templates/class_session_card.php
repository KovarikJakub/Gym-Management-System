<?php


/**
 * Formats the duration of a class session.
 * 
 * @param array $session The session data containing 'start_time' and 'end_time'.
 * @return string Formatted duration (e.g., "10:00 - 11:30").
 */
function formatClassDuration($session)
{
    $start = date('H:i', strtotime($session['start_time']));
    $end = date('H:i', strtotime($session['end_time']));
    return "$start - $end";
}

function formatAvailabality($session)
{
    $current = $session['current_enrollment'];
    $max = $session['default_capacity'];

    return "$current / $max";
}

function canEnroll($session, $today)
{
    $session_date = date('Y-m-d', strtotime($session['start_time']));
    // session is not full and is not in the past:
    return !$session['is_full'] && !($session_date < $today);
}

function outputClassSessionCard($session, $today)
{ ?>
<article class="card <?= !canEnroll($session, $today) ? 'card-disabled' : '' ?>">
    <header class="card-header">
        <h2 class="card-title"><?= htmlspecialchars($session['class_name']) ?></h2>
        <span class="badge badge-<?= str_replace(' ', '-', $session['class_type']) ?>">
            <?= htmlspecialchars($session['class_type']) ?>
        </span>
    </header>

    <div class="card-body">
        <p class="session-time">
            <strong>Time:</strong> <?= formatClassDuration($session) ?>
        </p>
        <p class="session-trainer">
            <strong>Trainer:</strong> <?= htmlspecialchars($session['trainer_name']) ?>
        </p>
        
        <div class="card-details">
            <div class="detail-item">
                <strong>Availability</strong>
                <span class="enrollment-info"><?= formatAvailabality($session) ?></span>
            </div>
            <div class="detail-item">
                <strong>Status</strong>
                <span class="status-info <?= $session['is_full'] ? 'text-danger' : 'text-success' ?>">
                    <?= $session['is_full'] ? 'Full' : $session['spots_left'] . ' left' ?>
                </span>
            </div>
        </div>

        <?php if (canEnroll($session, $today)): ?>
            <button class="btn-enroll" data-id="<?= $session['session_id'] ?>" >Enroll Now</button>
        <?php else: ?>
            <button class="btn-enroll" disabled>Not available</button>
        <?php endif; ?>
    </div>
</article>
<?php }