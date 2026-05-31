<?php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/models/users.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!verifyCsrf($_POST['csrf_token'] ?? ''))
    {
        die("CSRF token validation failed.");
    }

    $realName = $_POST['real_name'];
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $role     = 'member'; // default role

    // Basic Validation
    if (empty($realName) || empty($username) || empty($email) || empty($password))
    {
        $error = "All fields are required.";
        exit();
    }

    try
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // crate user entry in db
        createUser($realName, $username, $hashedPassword, $email, $role);
        // redirect to login page
        header("Location: login.php?registered=1");
        exit();
    }
    catch (PDOException $e)
    {
        if (strpos($e->getMessage(), 'users.username') !== false)
            $error = "Username already exists.";
        elseif (strpos($e->getMessage(), 'users.email') !== false)
            $error = "Email already exists.";
        else
            $error = "An error occurred during registration. Please try again.";
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
    <title>Register - Fit-Pro</title>
</head>
<body>
    <?php 
        require_once __DIR__ . '/../src/templates/common.php';
    ?>
    <main class="center-content">
        <div class="auth-container boxes">
            <h1>Register</h1>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <?php if ($error): ?>
                    <p style="color: #f44336; background: rgba(244, 67, 54, 0.1); padding: 10px; border-radius: 5px;">
                        <?= htmlspecialchars($error) ?>
                    </p>
                <?php endif; ?>

                <label for="real_name">Full Name</label>
                <input type="text" id="real_name" name="real_name" placeholder="John Doe" required>

                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="johndoe_1" required>

                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="johndoe@email.com" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>

                <button type="submit" class="btn">Create Account</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </main>
    <?php outputFooter(); ?>
</body>
</html>
