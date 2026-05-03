<?php

session_start();

function requireLogin()
{
    if (!isset($_SESSION['username']))
    {
        header('Location: login.php');
        exit;
    }
}
