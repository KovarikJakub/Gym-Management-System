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
<html>
<body>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <h1>Register</h1>
        <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <input type="text" name="real_name" placeholder="Full Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Create Account</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>