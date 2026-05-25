<?php

require_once __DIR__ . '/../database.php';

function getAllClassTypes()
{
    $db = getDbConnection();
    $stmt = $db->query("SELECT DISTINCT type FROM gym_classes");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
