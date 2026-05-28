<?php

/**
 * Outputs the common site header.
 */
function outputHeader()
{
    $isLoggedIn = isset($_SESSION['user_id']);
    ?>
    <header>
        <div>
            <h1><a href="index.html">Fit-Pro</a></h1>
        </div>

        <nav id="menu">
            <!-- just for the hamburger menu in responsive layout -->
            <input type="checkbox" id="hamburger"> 
            <label class="hamburger" for="hamburger"></label>
            <ul>
                <li><a href="sessions.php">Schedule</a></li>
                <li><a href="equipment.php">Equipment</a></li>
                <li><a href="trainers.php">Trainers</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="edit_profile.php">Profile</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div id="signup">
            <?php if ($isLoggedIn): ?>
                <a href="logout.php"><h2>Logout</h2></a>
            <?php else: ?>
                <a href="login.php"><h2>Login</h2></a>
                <a href="register.php"><h2>Register</h2></a>
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
