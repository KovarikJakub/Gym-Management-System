<?php

session_start();
session_destroy();

// redirect back to login page
$location = "login.php";
header("Location: " . $location);
exit();
