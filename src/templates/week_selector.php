<?php function outputWeekSelector($week_days, $selected_date, $today)
{ ?>
    <nav class="week-selector">
        <?php foreach ($week_days as $day): ?>
            <a href="?date=<?= $day['date'] ?>" 
               class="day-item <?= $day['date'] === $selected_date ? 'active' : '' ?> <?= $day['date'] === $today ? 'today-highlight' : '' ?>">
                <span class="day-name"><?= $day['day_name'] ?></span>
                <span class="day-date"><?= $day['day_num'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
<?php } ?>