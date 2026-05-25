<?php

function formatDuration($startTime, $endTime)
{
    $diffSeconds = $endTime - $startTime;
    $hours = floor($diffSeconds / 3600);
    $minutes = floor(($diffSeconds % 3600) / 60);
    
    $duration = sprintf('%d:%02d', $hours, $minutes);
    return $duration;
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

function outputClassSessionCard($session, $today)
{ 
    $is_past = date('Y-m-d', strtotime($session['start_time'])) < $today;
    ?>
<article class="card <?= $session['is_enrolled'] ? 'card-enrolled' : '' ?> <?= !canEnroll($session, $today) && !$session['is_enrolled'] ? 'card-disabled' : '' ?>">
    <header class="card-header">
        <a href="gym_class_detail.php?gym_class_id=<?= $session['gym_class_id'] ?>">
            <h2 class="card-title"><?= htmlspecialchars($session['class_name']) ?></h2>
        </a>
        <span class="badge badge-<?= str_replace(' ', '-', $session['class_type']) ?>">
            <?= htmlspecialchars($session['class_type']) ?>
        </span>
    </header>

    <div class="card-body">
        <p class="session-time">
            <?= formatClassDuration($session) ?>
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

        <?php if ($session['is_enrolled']): ?>
            <button class="btn-cancel" data-id="<?= $session['session_id'] ?>" <?= $is_past ? 'disabled' : '' ?>>
                <?= $is_past ? 'Already Attended' : 'Cancel Enrollment' ?>
            </button>
        <?php elseif (canEnroll($session, $today)): ?>
            <button class="btn-enroll" data-id="<?= $session['session_id'] ?>" >Enroll Now</button>
        <?php else: ?>
            <button class="btn-enroll" disabled>Not available</button>
        <?php endif; ?>
    </div>
</article>
<?php } ?>