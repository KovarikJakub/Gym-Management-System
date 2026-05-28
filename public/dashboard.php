<?php

require_once __DIR__ . '/../src/auth.php';

requireLogin();
?>

<!DOCTYPE html>
<html>
<body>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    <p>This is a protected dashboard area.</p>
    <nav>
        <h3><a href="profile.php">Profile</a></h3>
        <h3><a href="logout.php">Logout</a></h3>
    </nav>
</body>
</html>
