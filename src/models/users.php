<?php

require_once __DIR__ . '/../database.php';

function getUserByUsername($username)
{
    $db = getDbConnection();
    // Retrieve user profile by username
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    return $stmt->fetch();
}

function createUser($realName, $username, $hashedPassword, $email, $role, $profilePhoto = null)
{
    $db = getDbConnection();
    // Register new user
    $sql = "INSERT INTO users (real_name, username, password_hash, email, role, profile_photo) 
            VALUES (:real_name, :username, :password_hash, :email, :role, :profile_photo)";
            
    $stmt = $db->prepare($sql);
    return $stmt->execute([
        ':real_name'     => $realName,
        ':username'      => $username,
        ':password_hash' => $hashedPassword,
        ':email'         => $email,
        ':role'          => $role,
        ':profile_photo' => $profilePhoto
    ]);
}

function getAllTrainers()
{
    $db = getDbConnection();
    // Fetch only active trainers
    $stmt = $db->query("SELECT id, real_name FROM users WHERE role = 'trainer' AND is_deactivated = 0");
    return $stmt->fetchAll();
}

function getUserById($id)
{
    $db = getDbConnection();
    
    $stmt = $db->prepare("
        SELECT users.*, trainer_profiles.bio, trainer_profiles.specializations, trainer_profiles.certifications 
        FROM users 
        LEFT JOIN trainer_profiles ON users.id = trainer_profiles.user_id 
        WHERE users.id = ?
    ");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    return $user;
}

function updateUser($id, $realName, $username, $passwordHash = null, $profilePhoto = null, $deletePhoto = false)
{
    $db = getDbConnection();
    
    // Dynamic update query builder
    $sql = "UPDATE users SET real_name = :real_name, username = :username";
    $params = [':real_name' => $realName, ':username' => $username, ':id' => $id];
    
    if ($passwordHash) {
        $sql .= ", password_hash = :password";
        $params[':password'] = $passwordHash;
    }
    
    if ($deletePhoto) {
        $sql .= ", profile_photo = NULL";
    } elseif ($profilePhoto) {
        $sql .= ", profile_photo = :photo";
        $params[':photo'] = $profilePhoto;
    }
    
    $sql .= " WHERE id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

function getAllNonAdminUsers() 
{
    $db = getDbConnection();
    // List all members and trainers
    $sql = "SELECT id, real_name, username, email, role, is_deactivated 
            FROM users 
            WHERE role != 'admin' 
            ORDER BY role, real_name";
    return $db->query($sql)->fetchAll();
}

function updateUserRole($id, $role) 
{
    $db = getDbConnection();
    // Change user access level
    $stmt = $db->prepare("UPDATE users SET role = :role WHERE id = :id");
    return $stmt->execute([':role' => $role, ':id' => $id]);
}

function getUserRole($id) 
{
    $db = getDbConnection();
    // Verify user role
    $stmt = $db->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetchColumn();
}

function toggleUserStatus($id) 
{
    $db = getDbConnection();
    // Switch active/deactivated flag
    $stmtUpdate = $db->prepare("UPDATE users SET is_deactivated = NOT is_deactivated WHERE id = :id AND role != 'admin'");
    $stmtUpdate->execute([':id' => $id]);
    
    // Return the new status
    $stmtCheck = $db->prepare("SELECT is_deactivated FROM users WHERE id = :id");
    $stmtCheck->execute([':id' => $id]);
    return (bool)$stmtCheck->fetchColumn();
}