<?php

session_start();

// Generate CSRF token if it doesnt exist
if (empty($_SESSION['csrf_token']))
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verifyCsrf($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function requireLogin()
{
    if (!isset($_SESSION['username']))
    {
        header('Location: login.php');
        exit;
    }
}

function requireRole($role)
{
    requireLogin();

    if (($_SESSION['role'] ?? null) !== $role)
    {
        http_response_code(403);
        die('Access denied.');
    }
}
