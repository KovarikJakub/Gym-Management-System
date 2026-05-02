<?php
session_start();

require_once __DIR__ . '/../models/user.php';

if(verifyUserCredentials($_POST['username'], $_POST['password']))
{
    $_SESSION['username'] = $_POST['username'];
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
?>