<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php';

$error = null;
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['registered']))
{
    $message = "Registration successful! You can now log in.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!verifyCsrf($_POST['csrf_token'] ?? ''))
    {
        die("CSRF token validation failed.");
    }

    $user = getUserByUsername($_POST['username']);

    if ($user && password_verify($_POST['password'], $user['password_hash']))
    {
        // Regenerate ID to prevent session fixation
        session_regenerate_id();
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        
        header("Location: dashboard.php");
        exit();
    }
    else
    {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/layout.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <title>Login - Fit-Pro</title>
</head>
<body>
    <?php 
        require_once __DIR__ . '/../src/templates/common.php';
    ?>
    <main class="center-content">
        <div class="auth-container boxes">
            <h1>Login</h1>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <?php if ($message): ?>
                    <p style="color: #4CAF50; background: rgba(76, 175, 80, 0.1); padding: 10px; border-radius: 5px;">
                        <?= htmlspecialchars($message) ?>
                    </p>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <p style="color: #f44336; background: rgba(244, 67, 54, 0.1); padding: 10px; border-radius: 5px;">
                        <?= htmlspecialchars($error) ?>
                    </p>
                <?php endif; ?>

                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
                
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                
                <button type="submit" class="btn">Sign In</button>
            </form>
            <p>Don't have an account yet? <a href="register.php">Register here</a></p>
        </div>
    </main>
    <?php outputFooter(); ?>
</body>
</html>