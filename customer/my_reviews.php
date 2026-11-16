<?php
require_once __DIR__ . '/../includes/config.php';
require_login();

$uid = (int)$_SESSION['user_id'];
$rows = db_all('SELECT r.*, b.business_name FROM reviews r JOIN businesses b ON r.business_id = b.id
                WHERE r.user_id = ? ORDER BY r.created_at DESC', 'i', [$uid]);

$pageTitle = "My Reviews | ConnectZW";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="page-hero">
    <div class="container">
        <h1>⭐ My Reviews</h1>
        <p>Reviews you have written for businesses.</p>
    </div>
</div>

<section>
    <div class="container" style="max-width:820px">
        <?php if ($rows): foreach ($rows as $r): ?>
            <div class="panel">
                <div class="review-head">
                    <h3 style="margin:0"><a href="<?php echo e(url('business_profile.php?id=' . (int)$r['business_id'])); ?>"><?php echo e($r['business_name']); ?></a></h3>
                    <span class="stars"><?php echo stars($r['rating']); ?></span>
                </div>
                <p style="margin:10px 0"><?php echo nl2br(e($r['comment'])); ?></p>
                <small class="muted"><?php echo e(date('d M Y', strtotime($r['created_at']))); ?></small>
                <form method="POST" action="<?php echo e(url('delete_review.php')); ?>" class="inline-form" onsubmit="return confirm('Delete this review?');">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="review_id" value="<?php echo (int)$r['id']; ?>">
                    <input type="hidden" name="next" value="my_reviews">
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:10px">Delete</button>
                </form>
            </div>
        <?php endforeach; else: ?>
            <div class="empty-box">
                <div class="big">⭐</div>
                <h3>No reviews yet</h3>
                <p class="muted">Reviews you write will appear here.</p>
                <a class="btn" href="<?php echo e(url('search.php')); ?>">Find a business to review</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
