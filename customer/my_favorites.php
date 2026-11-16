<?php
require_once __DIR__ . '/../includes/config.php';
require_login();

$uid = (int)$_SESSION['user_id'];
$rows = db_all(BUSINESS_SELECT . ' JOIN favorites f ON f.business_id = b.id WHERE f.user_id = ? ORDER BY f.created_at DESC', 'i', [$uid]);

$pageTitle = "My Favorites | ConnectZW";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="page-hero">
    <div class="container">
        <h1>❤️ My Favorite Businesses</h1>
        <p>Businesses you saved for quick access.</p>
    </div>
</div>

<section>
    <div class="container">
        <div class="grid-3">
            <?php if ($rows): foreach ($rows as $b): ?>
                <div>
                    <?php business_card($b); ?>
                    <form method="POST" action="<?php echo e(url('remove_favorite.php')); ?>" style="margin-top:10px">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="business_id" value="<?php echo (int)$b['id']; ?>">
                        <input type="hidden" name="next" value="favorites">
                        <button type="submit" class="btn btn-danger btn-sm btn-block">Remove from favorites</button>
                    </form>
                </div>
            <?php endforeach; else: ?>
                <div class="empty-box">
                    <div class="big">❤️</div>
                    <h3>No favorites yet</h3>
                    <p class="muted">Save businesses you like and find them easily later.</p>
                    <a class="btn" href="<?php echo e(url('search.php')); ?>">Explore businesses</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
