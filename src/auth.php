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
