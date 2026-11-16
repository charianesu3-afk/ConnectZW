<?php
require_once __DIR__ . '/includes/config.php';
$posts = db_all('SELECT * FROM posts ORDER BY created_at DESC LIMIT 30');
$pageTitle = "Blog | ConnectZW";
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="page-hero"><div class="container"><h1>ConnectZW Blog</h1><p>Tips, news and stories for Zimbabwean businesses and customers.</p></div></div>
<section><div class="container">
    <div class="grid-3">
        <?php if ($posts): foreach ($posts as $po): $link = e(url('blog_post.php?slug=' . urlencode($po['slug']))); ?>
            <article class="post-card">
                <a class="post-img" href="<?php echo $link; ?>"><?php if ($po['image']): ?><img src="<?php echo e(upload_url($po['image'])); ?>" alt="" loading="lazy"><?php else: echo icon('file', 48); endif; ?></a>
                <div class="post-body"><time><?php echo e(date('M j, Y', strtotime($po['created_at']))); ?></time>
                    <h3><a href="<?php echo $link; ?>"><?php echo e($po['title']); ?></a></h3><p class="muted"><?php echo e($po['excerpt']); ?></p>
                    <a href="<?php echo $link; ?>" style="font-weight:600">Read more →</a></div>
            </article>
        <?php endforeach; else: ?><div class="empty-box"><?php echo icon('file', 40); ?><h3>No posts yet</h3><p class="muted">Check back soon.</p></div><?php endif; ?>
    </div>
</div></section>
<?php include 'includes/footer.php'; ?>
