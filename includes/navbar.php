<?php
$role = current_role();
$here = basename($_SERVER['SCRIPT_NAME'] ?? '');
$nav = ['index.php' => 'Home', 'categories.php' => 'Categories', 'search.php' => 'Businesses', 'about.php' => 'About Us', 'blog.php' => 'Blog', 'contact.php' => 'Contact'];
?>
<nav class="navbar">
    <div class="nav-container">
        <a class="brand" href="<?php echo e(url('index.php')); ?>"><?php echo icon('pin', 26); ?> ConnectZW</a>

        <button class="menu-toggle" type="button" onclick="toggleMenu()" aria-label="Open navigation menu"><?php echo icon('menu', 26); ?></button>

        <div class="nav-links" id="navLinks">
            <div class="nav-main">
                <?php foreach ($nav as $file => $label): ?>
                    <a href="<?php echo e(url($file)); ?>" class="<?php echo $here === $file ? 'active' : ''; ?>"><?php echo e($label); ?></a>
                <?php endforeach; ?>
            </div>
            <div class="nav-user">
                <?php if (is_logged_in()): ?>
                    <?php if ($role === 'customer'): ?><a href="<?php echo e(url('customer/my_favorites.php')); ?>"><?php echo icon('heart', 16); ?> Favorites</a><?php endif; ?>
                    <a href="<?php echo e(dashboard_url()); ?>"><?php echo icon('layout', 16); ?> Dashboard</a>
                    <span class="nav-name"><?php echo e($_SESSION['name'] ?? 'User'); ?></span>
                    <a class="btn btn-sm btn-outline-light" href="<?php echo e(url('logout.php')); ?>">Logout</a>
                <?php else: ?>
                    <a href="<?php echo e(url('login.php')); ?>">Login</a>
                    <a class="btn btn-sm" href="<?php echo e(url('register.php')); ?>">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<?php flash_render(); ?>
