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
<html>
<body>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <h1>Login</h1>
        <?php if ($message): ?><p style="color:green;"><?= htmlspecialchars($message) ?></p><?php endif; ?>
        <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign In</button>
    </form>
    <p>Dont have an account yet? <a href="register.php">Register here</a></p>
</body>
</html>