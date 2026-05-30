<?php
require_once __DIR__ . '/week_selector.php';

function outputFilterTab($week_days, $selected_date, $today, $trainers, $class_types, $active_filters)
{
    ?>
    <div class="filter-tab-container">
        <?php outputWeekSelector($week_days, $selected_date, $today); ?>

        <form action="sessions.php" method="GET" class="additional-filters">
            <!-- Persist the selected date when changing other filters -->
            <input type="hidden" name="date" value="<?= htmlspecialchars($selected_date) ?>">

            <div class="filter-group">
                <label for="trainer_id">Trainer:</label>
                <select name="trainer_id" id="trainer_id">
                    <option value="">All Trainers</option>
                    <?php foreach ($trainers as $trainer): ?>
                        <option value="<?= $trainer['id'] ?>" <?= (isset($active_filters['trainer_id']) && $active_filters['trainer_id'] == $trainer['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($trainer['real_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="type">Class Type:</label>
                <select name="type" id="type">
                    <option value="">All Types</option>
                    <?php foreach ($class_types as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= (isset($active_filters['type']) && $active_filters['type'] === $type) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($type)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <noscript>
                <button type="submit" class="btn">Apply Filters</button>
            </noscript>
            
            <?php if (!empty($active_filters['trainer_id']) || !empty($active_filters['type'])): ?>
                <a href="?date=<?= $selected_date ?>" class="clear-filters">Clear Additional Filters</a>
            <?php endif; ?>
        </form>
    </div>
    <?php
}
