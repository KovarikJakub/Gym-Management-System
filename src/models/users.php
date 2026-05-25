<?php

require_once __DIR__ . '/../database.php';

function getUserByUsername($username)
{
    $db = getDbConnection();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    return $user;
}

function createUser($realName, $username, $hashedPassword, $email, $role, $profilePhoto = null)
{
    $db = getDbConnection();
    $stmt = $db->prepare("INSERT INTO users (real_name, username, password_hash, email, role, profile_photo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$realName, $username, $hashedPassword, $email, $role, $profilePhoto]);
}

function getAllTrainers()
{
    $db = getDbConnection();
    $stmt = $db->query("SELECT id, real_name FROM users WHERE role = 'trainer' AND is_deactivated = 0");
    return $stmt->fetchAll();
}

function getUserById($id)
{
    $db = getDbConnection();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    return $user;
}

function updateUser($id, $realName, $username, $passwordHash = null, $profilePhoto = null, $deletePhoto = false)
{
    $db = getDbConnection();
    
    $sql = "UPDATE users SET real_name = ?, username = ?";
    $params = [$realName, $username];
    
    if ($passwordHash)
    {
        $sql .= ", password_hash = ?";
        $params[] = $passwordHash;
    }
    
    if ($deletePhoto)
    {
        $sql .= ", profile_photo = NULL";
    }
    elseif ($profilePhoto)
    {
        $sql .= ", profile_photo = ?";
        $params[] = $profilePhoto;
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $id;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
}