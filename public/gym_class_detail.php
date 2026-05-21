<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/reviews.php';
require_once __DIR__ . '/../src/templates/review_card.php';
require_once __DIR__ . '/../src/templates/review_form.php';

$reviews = [];
$gym_class_id = $_GET['gym_class_id'] ?? null;

if(!empty($gym_class_id))
{
    $reviews = getReviewsForGymClass($gym_class_id);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link rel="stylesheet" href="css/gym_class_detail.css">
    <title>Gym Class Detail</title>
</head>
<body>
    <header>
        <div>
            <h1><a href="index.html">Fit-Pro</a></h1>
        </div>

        <nav id="menu">
            <!-- just for the hamburguer menu in responsive layout -->
            <input type="checkbox" id="hamburger"> 
            <label class="hamburger" for="hamburger"></label>
            <ul>
                <li><a href="classes.html">Classes</a></li>
                <li><a href="equipment.html">Equipment</a></li>
                <li><a href="trainers.html">Trainers</a></li>
                <li><a href="schedule.html">Schedule</a></li>
            </ul>
        </nav>

        <div id="signup">
            <!-- 'buttons', add padding ... -->
            <a href="login.html"><h2>Login</h2></a>
            <a href="register.html"><h2>Register</h2></a>
        </div>
    </header>
    <main>
        <section class="back-button-section">
            <a href="#" class="back-button">← Back to classes</a>
        </section>
        <section class="gym-class-detail-container">
            <div class="image-section">
                <img src="assets/img/gym_class.jpg" alt="HIIT Bootcamp Class" />
            </div>

            <div class="content-section">
                <h1>HIIT Bootcamp</h1>
                
                <div class="meta-info">
                    <p class="author-name">with {Author Name}</p>
                    <div class="rating">
                        ⭐ 4.8 (127 Reviews)
                    </div>
                </div>

                <p class="description">
                    High-intensity interval training designed to maximize calorie burn and build endurance. Perfect for those who want to push their limits.
                </p>

                <div class="gym-class-attributes-container">
                    <div class="attribute-box boxes">
                        <span class="attribute-label">🕒 Duration</span>
                        <span class="attribute-value">45min</span>
                    </div>
                    <div class="attribute-box boxes">
                        <span class="attribute-label">📅 Sessions</span>
                        <span class="attribute-value">3/week</span>
                    </div>
                    <div class="attribute-box boxes">
                        <span class="attribute-label">👤 Capacity</span>
                        <span class="attribute-value">20</span>
                    </div>
                </div>
                
                <div class="book-button-container">
                    <button class="book-button btn">
                        Enroll Now
                    </button>
                </div>
            </div>
        </section>
        <section class="gym-class-reviews-container">
            <h1>Reviews</h1>
            
            <?php outputReviewForm($gym_class_id) ?>

            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                    <?php outputReviewCard($review) ?>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <footer class="site-footer">
        <p>&copy; 2026 GymFit Studio. All rights reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
        </div>
    </footer>
</body>
</html>