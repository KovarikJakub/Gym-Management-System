<?php
function outputAdminScheduleMenu() {
    ?>
    <section class="admin-schedule-menu">
        <button class="btn active" onclick="switchAdminTab('view-schedule')" id="btn-view-schedule">1. View Schedule</button>
        <button class="btn" onclick="switchAdminTab('add-session')" id="btn-add-session">2. Schedule Session</button>
    </section>
    <?php
}

function outputAdminScheduleForm($trainers) {
    $db = getDbConnection(); 
    $classTemplates = $db->query("
        SELECT id, name FROM gym_classes ORDER BY name ASC
    ")->fetchAll();
    ?>
    <div id="add-session" class="admin-tab admin-tab-padded" style="display: none;">
        <article class="boxes">
            <h2>Schedule a Session</h2>
            <form class="admin-form" id="sessionForm">
                <label class="admin-label">Select Class Type
                    <select class="admin-input admin-select" name="class_id" required>
                        <?php foreach ($classTemplates as $class): ?>
                            <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="admin-label">Assign Trainer
                    <select class="admin-input admin-select" name="trainer_id" required>
                        <?php foreach ($trainers as $trainer): ?>
                            <option value="<?= $trainer['id'] ?>"><?= htmlspecialchars($trainer['real_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="admin-label">Date & Time
                    <input class="admin-input" type="datetime-local" name="start_time" required>
                </label>

                <button type="submit" class="btn">Publish to Schedule</button>
            </form>
        </article>
    </div>

    <script>
        function switchAdminTab(tabId) {
            document.querySelectorAll('.admin-tab').forEach(el => el.style.display = 'none');
            document.getElementById(tabId).style.display = 'block';
            document.querySelectorAll('button[id^="btn-"]').forEach(btn => btn.style.opacity = '0.5');
            document.getElementById('btn-' + tabId).style.opacity = '1';
        }
    </script>
    <?php
}
?>
