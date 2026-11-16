<?php
require_once __DIR__ . '/includes/config.php';

if (is_logged_in()) redirect('dashboard.php');

$error = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please enter your email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $user = db_one('SELECT id, name, password, role FROM users WHERE email = ? LIMIT 1', 's', [$email]);
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];
            flash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect('dashboard.php');
        }
        $error = "Invalid email or password.";
    }
}

$pageTitle = "Login | ConnectZW";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <h1>Welcome back</h1>
        <p class="sub">Log in to your ConnectZW account</p>

        <?php if ($error !== ""): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>

        <form method="POST">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo e($email); ?>" required autocomplete="email">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-block">Log in</button>
        </form>

        <p class="center muted mt">New to ConnectZW? <a href="<?php echo e(url('register.php')); ?>">Create an account</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
