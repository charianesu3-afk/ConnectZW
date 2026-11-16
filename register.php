<?php
require_once __DIR__ . '/includes/config.php';

if (is_logged_in()) redirect('dashboard.php');

$error = "";
$name = $email = "";
$role = "customer";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $role     = $_POST['role'] ?? 'customer';

    // Only these two roles can ever be chosen on the form. Admins are created manually.
    if (!in_array($role, ['customer', 'business'], true)) $role = 'customer';

    if ($name === '' || $email === '' || $password === '') {
        $error = "Please fill in all fields.";
    } elseif (strlen($name) > 100) {
        $error = "Your name is too long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Your password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "The passwords do not match.";
    } elseif (db_one('SELECT id FROM users WHERE email = ?', 's', [$email])) {
        $error = "That email is already registered.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        db_exec('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)', 'ssss', [$name, $email, $hash, $role]);
        flash('success', 'Account created! You can now log in.');
        redirect('login.php');
    }
}

$pageTitle = "Register | ConnectZW";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <h1>Create your account</h1>
        <p class="sub">Join ConnectZW as a customer or business owner</p>

        <?php if ($error !== ""): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>

        <form method="POST">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="<?php echo e($name); ?>" maxlength="100" required>
            </div>
            <div class="field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?php echo e($email); ?>" maxlength="150" required>
            </div>
            <div class="field">
                <label for="password">Password <small class="muted">(8+ characters)</small></label>
                <input type="password" id="password" name="password" minlength="8" required autocomplete="new-password">
            </div>
            <div class="field">
                <label for="confirm">Confirm password</label>
                <input type="password" id="confirm" name="confirm" minlength="8" required autocomplete="new-password">
            </div>
            <div class="field">
                <label for="role">I am a...</label>
                <select name="role" id="role">
                    <option value="customer" <?php echo $role === 'customer' ? 'selected' : ''; ?>>Customer looking for businesses</option>
                    <option value="business" <?php echo $role === 'business' ? 'selected' : ''; ?>>Business owner</option>
                </select>
            </div>
            <button type="submit" class="btn btn-block">Create account</button>
        </form>

        <p class="center muted mt">Already have an account? <a href="<?php echo e(url('login.php')); ?>">Log in</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
