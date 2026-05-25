<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/gym_classes.php';
require_once __DIR__ . '/../src/models/reviews.php';
require_once __DIR__ . '/../src/templates/review_card.php';
require_once __DIR__ . '/../src/templates/common.php';

$gym_class_id = $_GET['gym_class_id'] ?? null;
$gym_class = null;
$reviews = [];

if(!empty($gym_class_id))
{
    $gym_class = getGymClassById($gym_class_id);
    if ($gym_class) {
        $reviews = getReviewsForGymClass($gym_class_id);
    }
}

if (!$gym_class) {
    die("Gym class not found.");
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
    <title><?= htmlspecialchars($gym_class['name']) ?> - Fit-Pro</title>
</head>
<body>
    <?php outputHeader(); ?>

    <main>
        <section class="back-button-section">
            <a href="sessions.php" class="back-button">← Back to schedule</a>
        </section>
        <section class="gym-class-detail-container">
            <div class="image-section">
                <img src="assets/img/gym_class.jpg" alt="<?= htmlspecialchars($gym_class['name']) ?>" />
            </div>

            <div class="content-section">
                <h1><?= htmlspecialchars($gym_class['name']) ?></h1>
                
                <div class="meta-info">
                    <p class="class-type"><?= htmlspecialchars(ucfirst($gym_class['type'])) ?></p>
                    <div class="rating">
                        ⭐ <?= $gym_class['avg_rating'] ? number_format($gym_class['avg_rating'], 1) : 'N/A' ?> 
                        (<?= $gym_class['review_count'] ?> Reviews)
                    </div>
                </div>

                <p class="description">
                    <?= htmlspecialchars($gym_class['description']) ?>
                </p>

                <div class="gym-class-attributes-container">
                    <div class="attribute-box boxes">
                        <span class="attribute-label">📊 Difficulty</span>
                        <span class="attribute-value"><?= $gym_class['difficulty'] ?>/5</span>
                    </div>
                    <div class="attribute-box boxes">
                        <span class="attribute-label">👤 Capacity</span>
                        <span class="attribute-value"><?= $gym_class['default_capacity'] ?></span>
                    </div>
                </div>
                
                <div class="book-button-container">
                    <a href="sessions.php" class="book-button btn">
                        View Schedule & Enroll
                    </a>
                </div>
            </div>
        </section>
        <section class="gym-class-reviews-container">
            <h1>Reviews</h1>
            
            <div class="reviews-list">
                <?php if (empty($reviews)): ?>
                    <p>No reviews yet for this class.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <?php outputReviewCard($review) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php outputFooter(); ?>
</body>
</html>
