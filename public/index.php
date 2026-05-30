<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/templates/common.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym managment</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
</head>
<body>
    <?php outputHeader(); ?>

    <main>
        <section id="presentation">
            <div class="overlay">
                <h1>Welcome to Fit-Pro</h1>
                <p>Your only limit is you mind!</p>
            </div>
        </section>

        <section id="shortcuts">
            <article class="boxes">
                <h3>Classes</h3>
                <ul>
                    <li>Yoga - schedule</li>
                    <li>HIIT -schedule</li>
                </ul>
                <br>
                <a href="gym_class_detail.php" class="btn">View Classes</a>
            </article>

            <article class="boxes">
                <h3>Equipment</h3>
                <ul>
                    <li>Treadmills - 3/5</li>
                    <li>Legpress - 2/4</li>
                </ul>
                <br>
                <a href="equipment.php" class="btn">Check Equipment</a>
            </article>

            <article class="boxes">
                <h3>Trainers</h3>
                <ul>
                    <li>Photo - Name</li>
                    <li>Photo - Name</li>
                </ul>
                <br>
                <a href="trainers.php" class="btn">Book Trainers</a>
            </article>
            <br>
        </section>
        <section id="info">
            <h1></h1>
            <article class="boxes">
                <h2>Zones</h2>
                <ul>
                    <li>Cardio Zone</li>
                    <li>Yoga Room</li>
                    <li>Strength Hall</li>
                </ul>
            </article>
            <article class="boxes">
                <h2>Opening Hours</h2>
                <ul>
                    <li>Monday-Friday: 6:00 - 22:00</li>
                    <li>Saturday: 8:00 - 20:00</li>
                    <li>Sunday: 8:00 - 18:00</li>
                </ul>
            </article>
            <br><br><br>
        </section>
    </main>
    <?php outputFooter(); ?>
</body>
</html>
