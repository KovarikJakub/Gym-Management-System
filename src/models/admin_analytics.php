<?php
require_once __DIR__ . '/../database.php';

function getAdminAnalyticsSummary()
{
    $db = getDbConnection();

    $sql = "SELECT
                (SELECT COUNT(*) FROM users WHERE role = 'member') AS total_members,
                (SELECT COUNT(*) FROM users WHERE role = 'trainer') AS total_trainers,
                (SELECT COUNT(*) FROM gym_classes) AS total_classes,
                (SELECT COUNT(*) FROM class_sessions) AS total_sessions,
                (SELECT COUNT(*) FROM enrollments) AS total_enrollments,
                (SELECT ROUND(AVG(rating), 1) FROM reviews) AS average_rating";

    return $db->query($sql)->fetch();
}

function getMostPopularClasses($limit = 5)
{
    $db = getDbConnection();

    $sql = "SELECT
                c.name,
                c.type,
                COUNT(DISTINCT s.id) AS sessions_count,
                COUNT(e.member_id) AS enrollments_count,
                (
                    SELECT ROUND(AVG(r.rating), 1)
                    FROM reviews r
                    JOIN class_sessions reviewed_session ON reviewed_session.id = r.session_id
                    WHERE reviewed_session.class_id = c.id
                ) AS average_rating
            FROM gym_classes c
            LEFT JOIN class_sessions s ON s.class_id = c.id
            LEFT JOIN enrollments e ON e.session_id = s.id
            GROUP BY c.id
            ORDER BY enrollments_count DESC, sessions_count DESC, c.name ASC
            LIMIT :limit";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function getTrainerAnalytics($limit = 5)
{
    $db = getDbConnection();

    $sql = "SELECT
                u.real_name AS trainer_name,
                COUNT(DISTINCT s.id) AS sessions_count,
                COUNT(e.member_id) AS enrollments_count,
                (
                    SELECT ROUND(AVG(r.rating), 1)
                    FROM reviews r
                    JOIN class_sessions reviewed_session ON reviewed_session.id = r.session_id
                    WHERE reviewed_session.trainer_id = u.id
                ) AS average_rating
            FROM users u
            LEFT JOIN class_sessions s ON s.trainer_id = u.id
            LEFT JOIN enrollments e ON e.session_id = s.id
            WHERE u.role = 'trainer'
            GROUP BY u.id
            ORDER BY enrollments_count DESC, average_rating DESC, u.real_name ASC
            LIMIT :limit";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}
