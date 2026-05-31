<?php

include_once __DIR__ . '/user_badge.php';

function formatDuration($startTime, $endTime)
{
    $diffSeconds = $endTime - $startTime;
    $hours = floor($diffSeconds / 3600);
    $minutes = floor(($diffSeconds % 3600) / 60);
    
    $duration = sprintf('%d:%02d', $hours, $minutes);
    return $duration;
}

function formatDate($session)
{
    return date('jS \of F Y', strtotime($session['start_time']));
}

function formatClassDuration($session)
{
    $startTime = strtotime($session['start_time']);
    $endTime = strtotime($session['end_time']);
    
    $start = date('G:i', $startTime);
    $end = date('G:i', $endTime);
    
    $duration = formatDuration($startTime, $endTime);
    
    return "$start - $end ($duration hrs)";
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

function outputClassSessionCard($session, $today, $isAdmin = false)
{ 
    $is_past = date('Y-m-d', strtotime($session['start_time'])) < $today;
    // Map session trainer data to standard user object for user_badge template
    $trainer = [
        'id' => $session['trainer_id'],
        'username' => $session['trainer_name'],
        'profile_photo' => $session['trainer_profile_photo'],
        'role' => 'trainer'
    ];
    ?>
<article class="card <?= $session['is_enrolled'] ? 'card-enrolled' : '' ?> <?= !canEnroll($session, $today) && !$session['is_enrolled'] ? 'card-disabled' : '' ?>">
    <header class="card-header">
        <a href="gym_class_detail.php?gym_class_id=<?= $session['gym_class_id'] ?>" class="card-title-link">
            <h2 class="card-title"><?= htmlspecialchars($session['class_name']) ?></h2>
            <span class="class-type-italic">
                (<?= htmlspecialchars($session['class_type']) ?>)
            </span>
        </a>
        <?php if ($session['is_enrolled']): ?>
            <div class="enrolled-status">
                <span class="status-dot"></span>
                <span class="status-label">Enrolled!</span>
            </div>
        <?php endif; ?>
    </header>

    <div class="card-body">
        <p class="session-time">
            <?= formatClassDuration($session) ?>
        </p>
        <p class="session-date">
            <?= formatDate($session) ?>
        </p>
        <p class="session-trainer">
            <?= outputUserBadge($trainer) ?>
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

        <div class="card-actions">
            <?php if ($session['is_enrolled']): ?>
                <button class="btn-cancel" data-id="<?= $session['session_id'] ?>" <?= $is_past ? 'disabled' : '' ?>>
                    <?= $is_past ? 'Already Attended' : 'Cancel Enrollment' ?>
                </button>
            <?php elseif (canEnroll($session, $today)): ?>
                <button class="btn-enroll" data-id="<?= $session['session_id'] ?>" >Enroll Now</button>
            <?php else: ?>
                <button class="btn-enroll" disabled>Not available</button>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <button class="btn-delete-session" data-id="<?= $session['session_id'] ?>">Delete Session</button>
            <?php endif; ?>
        </div>
    </div>
</article>
<?php } ?>