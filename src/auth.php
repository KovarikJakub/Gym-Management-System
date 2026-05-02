<?php

session_start();

function requireLogin()
{
    if (!isset($_SESSION['username']))
    {
        header('Content-Type: application/json', true, 401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}
