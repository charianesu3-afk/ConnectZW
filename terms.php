<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = "Terms of Use | ConnectZW";
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="page-hero"><div class="container"><h1>Terms of Use</h1><p>Please read these terms before using ConnectZW.</p></div></div>
<section><div class="container prose" style="max-width:820px">
<h2>1. About ConnectZW</h2><p>ConnectZW is an online directory that helps customers find businesses in Zimbabwe. We do not provide the services listed and are not a party to any agreement between a customer and a business.</p>
<h2>2. Accounts</h2><p>You are responsible for keeping your login details safe and for the information you post. Provide accurate information and do not impersonate another person or business.</p>
<h2>3. Listings and reviews</h2><p>Business owners must only list genuine businesses and keep their details accurate. Reviews must be honest and based on real experience. We may remove content that is false, abusive or illegal.</p>
<h2>4. Acceptable use</h2><p>Do not misuse the platform, attempt to access other people's data, upload harmful files or send spam.</p>
<h2>5. Changes</h2><p>We may update these terms from time to time. Continued use of ConnectZW means you accept the updated terms.</p>
<h2>6. Contact</h2><p>Questions about these terms? <a href="<?php echo e(url('contact.php')); ?>">Contact us</a>.</p>
<p class="muted" style="font-size:13px">This is a general template. Please have it reviewed by a legal professional before launch.</p>
</div></section>
<?php include 'includes/footer.php'; ?>
