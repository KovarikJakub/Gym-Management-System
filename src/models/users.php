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

function createUser($realName, $username, $hashedPassword, $email, $role)
{
    $db = getDbConnection();
    $stmt = $db->prepare("INSERT INTO users (real_name, username, password_hash, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$realName, $username, $hashedPassword, $email, $role]);
}