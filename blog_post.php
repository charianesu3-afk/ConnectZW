<?php
require_once __DIR__ . '/includes/config.php';
$po = db_one('SELECT * FROM posts WHERE slug = ?', 's', [$_GET['slug'] ?? '']);
if (!$po) { flash('error', 'That article could not be found.'); redirect('blog.php'); }
$pageTitle = $po['title'] . " | ConnectZW Blog";
include 'includes/header.php';
include 'includes/navbar.php';
?>
<article class="article prose">
    <p><a href="<?php echo e(url('blog.php')); ?>">← Back to blog</a></p>
    <h1><?php echo e($po['title']); ?></h1>
    <p class="muted"><?php echo e(date('F j, Y', strtotime($po['created_at']))); ?></p>
    <?php if ($po['image']): ?><img src="<?php echo e(upload_url($po['image'])); ?>" alt="" style="border-radius:12px;margin:10px 0 20px;width:100%"><?php endif; ?>
    <?php echo render_post($po['content']); ?>
    <div class="cta-card" style="margin-top:34px"><div><h3>Own a business?</h3><p>List it on ConnectZW and get found by local customers.</p><a class="btn" href="<?php echo e(url('register.php')); ?>">List Your Business</a></div></div>
</article>
<?php include 'includes/footer.php'; ?>
