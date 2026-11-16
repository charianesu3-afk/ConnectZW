<?php
require_once __DIR__ . '/../includes/config.php';
require_login();
if (current_role() !== 'customer') { header('Location: ' . dashboard_url()); exit(); }

$uid = (int)$_SESSION['user_id'];
$favCount = (int)db_one('SELECT COUNT(*) n FROM favorites WHERE user_id = ?', 'i', [$uid])['n'];
$revCount = (int)db_one('SELECT COUNT(*) n FROM reviews WHERE user_id = ?', 'i', [$uid])['n'];
$favs = db_all(BUSINESS_SELECT . ' JOIN favorites f ON f.business_id = b.id WHERE f.user_id = ? ORDER BY f.created_at DESC LIMIT 3', 'i', [$uid]);
$revs = db_all('SELECT r.*, b.business_name FROM reviews r JOIN businesses b ON r.business_id = b.id WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 4', 'i', [$uid]);

$pageTitle = "My Dashboard | ConnectZW";
include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container dash">
    <div class="dash-head"><h1>Welcome back, <?php echo e($_SESSION['name']); ?> 👋</h1><p>Here's what you've saved and reviewed on ConnectZW.</p></div>

    <div class="kpis" style="grid-template-columns:repeat(3,1fr)">
        <div class="kpi"><div class="k-top"><?php echo icon('heart', 16); ?> Saved businesses</div><strong><?php echo $favCount; ?></strong></div>
        <div class="kpi"><div class="k-top"><?php echo icon('star', 16); ?> Reviews written</div><strong><?php echo $revCount; ?></strong></div>
        <div class="kpi"><div class="k-top"><?php echo icon('search', 16); ?> Find more</div><a class="btn btn-sm" style="margin-top:6px" href="<?php echo e(url('search.php')); ?>">Browse businesses</a></div>
    </div>

    <div class="section-head"><h2>Saved businesses</h2><a href="<?php echo e(url('customer/my_favorites.php')); ?>">View all →</a></div>
    <div class="grid-3" style="margin-bottom:30px">
        <?php if ($favs): foreach ($favs as $b) business_card($b); else: ?>
            <div class="empty-box"><?php echo icon('heart', 36); ?><h3>No favorites yet</h3><p class="muted">Tap the heart on any business to save it here.</p></div>
        <?php endif; ?>
    </div>

    <div class="section-head"><h2>My recent reviews</h2><a href="<?php echo e(url('customer/my_reviews.php')); ?>">View all →</a></div>
    <div class="panel">
        <?php if ($revs): foreach ($revs as $r): ?>
            <div class="review"><div class="review-head"><strong><a href="<?php echo e(url('business_profile.php?id=' . (int)$r['business_id'])); ?>"><?php echo e($r['business_name']); ?></a></strong><span class="stars"><?php echo stars($r['rating']); ?></span></div>
                <p style="margin:6px 0 0"><?php echo e($r['comment']); ?></p></div>
        <?php endforeach; else: ?><p class="muted" style="margin:0">You haven't written any reviews yet.</p><?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
