<?php

require_once __DIR__ . '/../database.php';

function getAllClassTypes()
{
    $db = getDbConnection();
    // Fetch unique class categories
    $stmt = $db->query("SELECT DISTINCT type FROM gym_classes");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getGymClassById($id)
{
    $db = getDbConnection();
    
    // Retrieve class details with aggregated review data
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

function getAllGymClasses() 
{
    $db = getDbConnection();
    // Get all classes sorted alphabetically
    $stmt = $db->query("SELECT * FROM gym_classes ORDER BY name");
    return $stmt->fetchAll();
}

function createGymClass($name, $difficulty, $description, $defaultCapacity, $type) 
{
    $db = getDbConnection();
    // Insert new gym class record
    $sql = "INSERT INTO gym_classes (name, difficulty, description, default_capacity, type) 
            VALUES (:name, :difficulty, :description, :default_capacity, :type)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':name'             => $name,
        ':difficulty'       => $difficulty,
        ':description'      => $description,
        ':default_capacity' => $defaultCapacity,
        ':type'             => $type
    ]);
    return $db->lastInsertId();
}

function updateGymClass($id, $name, $type, $difficulty, $defaultCapacity, $description) 
{
    $db = getDbConnection();
    // Update existing gym class attributes
    $sql = "UPDATE gym_classes 
            SET name = :name, type = :type, difficulty = :difficulty, 
                default_capacity = :default_capacity, description = :description 
            WHERE id = :id";
            
    $stmt = $db->prepare($sql);
    return $stmt->execute([
        ':name'             => $name,
        ':type'             => $type,
        ':difficulty'       => $difficulty,
        ':default_capacity' => $defaultCapacity,
        ':description'      => $description,
        ':id'               => $id
    ]);
}

function deleteGymClass($id) 
{
    $db = getDbConnection();
    // Remove gym class by ID
    $stmt = $db->prepare("DELETE FROM gym_classes WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}