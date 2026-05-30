<?php

require_once __DIR__ . '/../database.php';

/**
 * Get full equipment inventory list.
 */
function getAllEquipment() 
{
    $db = getDbConnection();
    $stmt = $db->query("SELECT * FROM equipment ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Add new equipment to inventory.
 */
function addEquipment($name, $status) 
{
    $db = getDbConnection();
    $stmt = $db->prepare("INSERT INTO equipment (name, status) VALUES (:name, :status)");
    $stmt->execute([':name' => $name, ':status' => $status]);
    return $db->lastInsertId();
}

/**
 * Modify existing equipment details.
 */
function updateEquipment($id, $name, $status) 
{
    $db = getDbConnection();
    $stmt = $db->prepare("UPDATE equipment SET name = :name, status = :status WHERE id = :id");
    return $stmt->execute([':name' => $name, ':status' => $status, ':id' => $id]);
}

/**
 * Remove equipment item from database.
 */
function deleteEquipment($id) 
{
    $db = getDbConnection();
    $stmt = $db->prepare("DELETE FROM equipment WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}