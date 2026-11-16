<?php
require_once __DIR__ . '/includes/config.php';
$faqs = [
 ['Is ConnectZW free to use?', 'Yes. Searching for businesses and creating a customer account is free, and listing your business is free during our launch.'],
 ['How do I list my business?', 'Click Sign Up, choose "Business owner", then fill in your business details in your dashboard. Your listing goes live straight away.'],
 ['What does the Verified badge mean?', 'Verified businesses have completed their profile and been checked by the ConnectZW team. Contact us to request verification.'],
 ['How do customers contact me?', 'Customers can call, WhatsApp, email or visit your website straight from your profile. You can see how many clicked in your dashboard.'],
 ['Can I add photos and list my services?', 'Yes. Your dashboard lets you upload photos, list the services you offer with optional prices, and set your opening hours.'],
 ['How do reviews work?', 'Logged-in customers can leave one review per business. Business owners cannot review their own listing.'],
 ['I forgot my password. What do I do?', 'Please contact support and we will help you recover your account.'],
];
$pageTitle = "FAQs & Help Center | ConnectZW";
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="page-hero"><div class="container"><h1>Help Center &amp; FAQs</h1><p>Answers to common questions about ConnectZW.</p></div></div>
<section><div class="container" style="max-width:820px"><div class="faq">
    <?php foreach ($faqs as $f): ?><details><summary><?php echo e($f[0]); ?></summary><p><?php echo e($f[1]); ?></p></details><?php endforeach; ?>
</div><p class="mt muted">Still need help? <a href="<?php echo e(url('contact.php')); ?>">Contact support</a>.</p></div></section>
<?php include 'includes/footer.php'; ?>
