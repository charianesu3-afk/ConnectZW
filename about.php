<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = "About ConnectZW | Connecting Zimbabwean Businesses";
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="page-hero"><div class="container"><h1>About ConnectZW</h1><p>Connecting Zimbabwean businesses with customers through a trusted digital platform.</p></div></div>
<section><div class="container">
    <div class="two-col" style="align-items:center">
        <div><h2>Who we are</h2>
            <p>ConnectZW is a business directory built to help people discover reliable businesses and services across Zimbabwe, from plumbers and mechanics to restaurants, salons and shops.</p>
            <p>Our goal is to bridge the gap between local businesses and customers by providing a simple, accessible and trusted way to connect: call, WhatsApp or email in one tap.</p></div>
        <div class="why-card"><h3>What you can do</h3><ul class="checklist">
            <?php foreach (['Find businesses by category, city or service', 'Read reviews and see photos before you choose', 'Save your favourite businesses', 'List your own business for free'] as $w): ?>
                <li><span class="ck"><?php echo icon('check', 14); ?></span><?php echo e($w); ?></li><?php endforeach; ?></ul></div>
    </div>
</div></section>
<section style="background:#fff"><div class="container">
    <div class="section-title"><h2>Our mission</h2><p>Making business discovery easier for everyone.</p></div>
    <div class="grid-3">
        <div class="card"><?php echo icon('search', 30); ?><h3 style="margin-top:10px">Easy discovery</h3><p class="muted">Help customers quickly find businesses, services and professionals near them.</p></div>
        <div class="card"><?php echo icon('trend', 30); ?><h3 style="margin-top:10px">Business growth</h3><p class="muted">Give small and growing businesses an online presence where they can reach more customers.</p></div>
        <div class="card"><?php echo icon('shield', 30); ?><h3 style="margin-top:10px">Trusted connections</h3><p class="muted">Help customers make informed decisions through business information, services and reviews.</p></div>
    </div>
</div></section>
<section><div class="container"><div class="cta-card"><div><h3>Are you a business owner?</h3><p>Create your ConnectZW profile and start reaching more customers today.</p><a class="btn" href="<?php echo e(url('register.php')); ?>">Join ConnectZW</a></div><div class="cta-art"><?php echo icon('store', 100); ?></div></div></div></section>
<?php include 'includes/footer.php'; ?>
