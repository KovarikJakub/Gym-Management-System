<?php

require_once __DIR__ . '/../database.php';

function getAllClassTypes()
{
    $db = getDbConnection();
    $stmt = $db->query("SELECT DISTINCT type FROM gym_classes");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getGymClassById($id)
{
    $db = getDbConnection();
    
    $sql = "SELECT 
                c.*,
                (SELECT COUNT(*) FROM reviews r 
                 JOIN class_sessions s ON r.session_id = s.id 
                 WHERE s.class_id = c.id) AS review_count,
                (SELECT AVG(r.rating) FROM reviews r 
                 JOIN class_sessions s ON r.session_id = s.id 
                 WHERE s.class_id = c.id) AS avg_rating
            FROM gym_classes c
            WHERE c.id = :id";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}
