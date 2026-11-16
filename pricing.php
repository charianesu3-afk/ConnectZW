<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = "Pricing | ConnectZW";
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="page-hero"><div class="container"><h1>Simple pricing</h1><p>Get discovered by local customers, free during our launch.</p></div></div>
<section><div class="container" style="max-width:860px"><div class="two-col">
    <div class="price-card pop"><h3>Free Listing</h3><div class="amt">$0</div>
        <ul class="checklist"><?php foreach (['Business profile page', 'Services, photos and opening hours', 'Call, WhatsApp, email and website buttons', 'Customer reviews', 'Performance dashboard'] as $w): ?><li><span class="ck"><?php echo icon('check', 14); ?></span><?php echo e($w); ?></li><?php endforeach; ?></ul>
        <p class="mt"><a class="btn btn-block" href="<?php echo e(url('register.php')); ?>">List Your Business</a></p></div>
    <div class="price-card"><h3>Featured Listing</h3><div class="amt" style="font-size:26px;margin-top:14px">Coming soon</div>
        <ul class="checklist"><?php foreach (['Everything in Free', 'Priority placement in search', 'Featured on the home page', 'Verified badge support'] as $w): ?><li><span class="ck" style="background:#CBD5E1"><?php echo icon('check', 14); ?></span><?php echo e($w); ?></li><?php endforeach; ?></ul>
        <p class="mt"><a class="btn btn-light btn-block" href="<?php echo e(url('contact.php')); ?>">Ask about it</a></p></div>
</div></div></section>
<?php include 'includes/footer.php'; ?>
