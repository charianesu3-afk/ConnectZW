<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = "Privacy Policy | ConnectZW";
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="page-hero"><div class="container"><h1>Privacy Policy</h1><p>How ConnectZW handles your information.</p></div></div>
<section><div class="container prose" style="max-width:820px">
<h2>Information we collect</h2><p>When you register we collect your name, email address and a securely hashed password. Business owners also provide business details such as phone number, address, photos and opening hours, which are shown publicly. If you subscribe to our newsletter or contact us, we store your email and message.</p>
<h2>How we use it</h2><p>We use your information to run your account, show business listings, display reviews, respond to enquiries and improve the platform. We also count visits and contact clicks on each business profile so owners can see their performance.</p>
<h2>Sharing</h2><p>We do not sell your personal information. Business details and reviews you post are visible to other visitors.</p>
<h2>Your choices</h2><p>You can ask us to correct or delete your account and data at any time by contacting support.</p>
<h2>Contact</h2><p>Privacy questions? <a href="<?php echo e(url('contact.php')); ?>">Contact us</a>.</p>
<p class="muted" style="font-size:13px">This is a general template. Please have it reviewed by a legal professional before launch.</p>
</div></section>
<?php include 'includes/footer.php'; ?>
