<?php

require_once __DIR__ . '/../database.php';


function getReviewsForGymClass($gym_class_id)
{
    $db = getDbConnection();

    $sql = "SELECT
        r.id AS review_id,
        r.member_id AS author_id,
        r.session_id AS session_id,
        r.rating AS rating,
        r.comment AS comment,
        r.posted_date AS posted_date,
        u.username AS username,
        u.real_name AS real_name,
        u.profile_photo as profile_photo
    FROM reviews r 
    JOIN class_sessions cs ON r.session_id = cs.id
    JOIN gym_classes gc ON cs.class_id = gc.id
    JOIN users u ON r.member_id = u.id
    WHERE gc.id = :gym_class_id
    ORDER BY r.posted_date DESC";

    $stmt = $db->prepare($sql);
    $params = [ ':gym_class_id' => $gym_class_id ];
    $stmt->execute($params);
    $reviews = $stmt->fetchAll();

    return $reviews;
}

/**
 * Gets all reviews for a specific class session.
 * 
 * @param int $session_id The ID of the class session.
 * @return array List of reviews.
 */
function getReviewsForSession($session_id)
{
    $db = getDbConnection();

    $sql = "SELECT
        r.id AS review_id,
        r.member_id AS author_id,
        r.session_id AS session_id,
        r.rating AS rating,
        r.comment AS comment,
        r.posted_date AS posted_date,
        u.username AS username,
        u.real_name AS real_name,
        u.profile_photo as profile_photo
    FROM reviews r 
    JOIN users u ON r.member_id = u.id
    WHERE r.session_id = :session_id
    ORDER BY r.posted_date DESC";

    $stmt = $db->prepare($sql);
    $params = [ ':session_id' => $session_id ];
    $stmt->execute($params);
    $reviews = $stmt->fetchAll();

    return $reviews;
}