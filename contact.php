<?php
require_once __DIR__ . '/includes/config.php';

$name = $email = $subject = $message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? ''); $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $subject === '' || $message === '') $error = "Please complete all fields.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Please enter a valid email address.";
    elseif (strlen($name) > 100 || strlen($email) > 150 || strlen($subject) > 200) $error = "One of your answers is too long.";
    elseif (strlen($message) > 5000) $error = "Your message is too long. Please keep it under 5000 characters.";
    else {
        db_exec('INSERT INTO contact_messages (name, email, subject, message) VALUES (?,?,?,?)', 'ssss', [$name, $email, $subject, $message]);
        flash('success', 'Your message has been sent. We will get back to you soon.');
        redirect('contact.php');
    }
}

$pageTitle = "Contact ConnectZW | Get In Touch";
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="page-hero"><div class="container"><h1>Contact ConnectZW</h1><p>We are here to help customers and businesses connect better.</p></div></div>
<section><div class="container">
    <div class="grid-3" style="margin-bottom:34px">
        <div class="card"><?php echo icon('mail', 28); ?><h3 style="margin-top:10px">Email support</h3><p class="muted" style="margin:0">support@connectzw.co.zw<br>Questions, account support and business help.</p></div>
        <div class="card"><?php echo icon('phone', 28); ?><h3 style="margin-top:10px">Phone support</h3><p class="muted" style="margin:0">+263 771 782 348<br>Business registration assistance.</p></div>
        <div class="card"><?php echo icon('pin', 28); ?><h3 style="margin-top:10px">Location</h3><p class="muted" style="margin:0">Zimbabwe<br>Connecting businesses nationwide.</p></div>
    </div>
    <div class="panel contact-form"><h2>Send us a message</h2>
        <?php if ($error !== ""): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
        <form method="POST"><?php echo csrf_field(); ?>
            <div class="grid-2"><div class="field"><label>Your name</label><input type="text" name="name" value="<?php echo e($name); ?>" maxlength="100" required></div>
                <div class="field"><label>Your email</label><input type="email" name="email" value="<?php echo e($email); ?>" maxlength="150" required></div></div>
            <div class="field"><label>Subject</label><input type="text" name="subject" value="<?php echo e($subject); ?>" maxlength="200" required></div>
            <div class="field"><label>Message</label><textarea name="message" maxlength="5000" required><?php echo e($message); ?></textarea></div>
            <button type="submit">Send message</button></form></div>
</div></section>
<?php include 'includes/footer.php'; ?>
