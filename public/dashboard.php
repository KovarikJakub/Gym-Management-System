<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/templates/common.php';

requireLogin();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Fit-Pro</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
</head>
<body>
    <?php outputHeader(); ?>

    <main style="padding: 20px;">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
        <p>This is a protected dashboard area.</p>
    </main>

    <?php outputFooter(); ?>
</body>
</html>