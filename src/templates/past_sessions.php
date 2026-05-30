<?php
require_once __DIR__ . '/past_session.php';

function outputPastSessions($sessions)
{
    if (empty($sessions)) {
        echo "<p>You haven't attended any sessions yet.</p>";
        return;
    }

    echo '<div class="past-sessions-list">';
    foreach ($sessions as $session) {
        outputPastSession($session);
    }
    echo '</div>';
}
