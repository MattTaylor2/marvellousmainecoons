<?php
// If no session started yet, start one so $_SESSION is available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <div class="header-left">
        <img src="/images/icon_image.jpg" alt="Site Logo" class="site-logo">
        <h1>Marvellous Maine Coons</h1>
    </div>
    <nav class="header-nav">
        <ul>
            <li>
                <a 
                  href="/index.php" 
                  class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>"
                >Home</a>
            </li>
            <li>
                <a 
                  href="/kittens.php" 
                  class="<?= basename($_SERVER['PHP_SELF']) === 'kittens.php' ? 'active' : '' ?>"
                >Kittens</a>
            </li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li>
                    <a 
                      href="/change-password.php" 
                      class="<?= basename($_SERVER['PHP_SELF']) === 'change-password.php' ? 'active' : '' ?>"
                    >Change Password</a>
                </li>
                <li>
                    <a 
                      href="/logout.php" 
                      class="<?= basename($_SERVER['PHP_SELF']) === 'logout.php' ? 'active' : '' ?>"
                    >Logout</a>
                </li>
            <?php else: ?>
                <li>
                    <a 
                      href="/login.php" 
                      class="<?= basename($_SERVER['PHP_SELF']) === 'login.php' ? 'active' : '' ?>"
                    >Login</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
