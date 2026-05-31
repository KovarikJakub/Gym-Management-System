<?php

/**
 * Outputs the shared center navigation menu.
 */
function outputNavigationMenu()
{
    $isLoggedIn = isset($_SESSION['user_id']);
    $isAdmin    = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    $currentPage = basename($_SERVER['PHP_SELF']);

    $links = [
        'sessions.php' => 'Schedule',
        'equipment.php' => 'Equipment',
        'trainers.php' => 'Trainers'
    ];

    if ($isLoggedIn) {
        $links['dashboard.php'] = 'Dashboard';
        $links['edit_profile.php'] = 'Profile';
    }

    if ($isAdmin) {
        $links['admin_analytics.php'] = 'Analytics';
        $links['admin_users.php'] = 'Users';
        $links['admin_classes.php'] = 'Classes';
    }
    ?>
    <ul>
        <?php foreach ($links as $url => $label): ?>
            <li>
                <a href="<?= $url ?>" class="<?= $currentPage === $url ? 'active' : '' ?>">
                    <?= $label ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
}

/**
 * Outputs the common site header.
 */
function outputHeader()
{
    $isLoggedIn = isset($_SESSION['user_id']);
    $currentPage = basename($_SERVER['PHP_SELF']);
    ?>
    <header>
        <div>
            <h1><a href="index.php">Fit-Pro</a></h1>
        </div>

        <nav id="menu">
            <!-- just for the hamburger menu in responsive layout -->
            <input type="checkbox" id="hamburger"> 
            <label class="hamburger" for="hamburger"></label>
            <?php outputNavigationMenu(); ?>
        </nav>

        <div id="signup">
            <?php if ($isLoggedIn): ?>
                <a href="logout.php"><h2>Logout</h2></a>
            <?php else: ?>
                <a href="login.php" class="<?= $currentPage === 'login.php' ? 'active-auth' : '' ?>"><h2>Login</h2></a>
                <a href="register.php" class="<?= $currentPage === 'register.php' ? 'active-auth' : '' ?>"><h2>Register</h2></a>
            <?php endif; ?>
        </div>
    </header>
    <?php
}

/**
 * Outputs the common site footer.
 */
function outputFooter()
{
    ?>
    <footer>
        <p>&copy; 2026 GymFit Studio. All rights reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
        </div>
    </footer>
    <?php
}
