<?php
require_once __DIR__ . '/includes/config.php';

$id = (int)($_GET['id'] ?? 0);
$b = $id > 0 ? db_one(BUSINESS_SELECT . ' WHERE b.id = ?', 'i', [$id]) : null;
if (!$b) { flash('error', 'That business could not be found.'); redirect('search.php'); }

// One view per visitor session
if (empty($_SESSION['viewed'][$id])) {
    db_exec('UPDATE businesses SET views = views + 1 WHERE id = ?', 'i', [$id]);
    track_stat($id, 'views');
    $_SESSION['viewed'][$id] = 1;
    $b['views']++;
}

$services = db_all('SELECT * FROM business_services WHERE business_id = ? ORDER BY id ASC', 'i', [$id]);
$gallery  = db_all('SELECT * FROM business_images WHERE business_id = ? ORDER BY id DESC', 'i', [$id]);
$reviews  = db_all('SELECT r.*, u.name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.business_id = ? ORDER BY r.created_at DESC', 'i', [$id]);

$uid = is_logged_in() ? (int)$_SESSION['user_id'] : 0;
$isOwner  = $uid && (int)$b['user_id'] === $uid;
$isFav    = $uid ? (bool)db_one('SELECT id FROM favorites WHERE user_id = ? AND business_id = ?', 'ii', [$uid, $id]) : false;
$myReview = $uid ? db_one('SELECT id FROM reviews WHERE user_id = ? AND business_id = ?', 'ii', [$uid, $id]) : null;

$similar = [];
if (!empty($b['category_id'])) {
    $similar = db_all(BUSINESS_SELECT . ' WHERE b.category_id = ? AND b.id <> ? ORDER BY RAND() LIMIT 3', 'ii', [$b['category_id'], $id]);
}

$cat   = $b['category_name'] ?? 'Business';
$coverImg = $gallery ? upload_url($gallery[0]['image']) : category_image($cat);
$tel   = preg_replace('/[^\d+]/', '', (string)$b['phone']);
$wa    = wa_number($b['phone']);
$st    = open_status($b);
$addr  = trim(($b['location'] ?: '') . ((!empty($b['city']) && stripos((string)$b['location'], $b['city']) === false) ? ', ' . $b['city'] : '') . ', Zimbabwe', ', ');
$site  = (!empty($b['website']) && preg_match('#^https?://#i', $b['website'])) ? $b['website'] : '';

$pageTitle = $b['business_name'] . " | ConnectZW";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="cover" <?php echo $coverImg ? 'style="background-image:url(\'' . e($coverImg) . '\')"' : ''; ?>>
    <div class="container">
        <a class="back-link" href="<?php echo e(url('search.php')); ?>">← Back to businesses</a>
        <?php if (is_logged_in() && !$isOwner): ?>
            <form class="fav-fab" method="POST" action="<?php echo e(url($isFav ? 'remove_favorite.php' : 'add_favorite.php')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="business_id" value="<?php echo $id; ?>">
                <button type="submit" class="<?php echo $isFav ? 'on' : ''; ?>" title="<?php echo $isFav ? 'Remove from favorites' : 'Save to favorites'; ?>"><?php echo icon('heart', 20); ?></button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <div class="biz-head">
        <div class="biz-id">
            <?php if (!empty($b['logo'])): ?>
                <img class="biz-logo" src="<?php echo e(upload_url($b['logo'])); ?>" alt="<?php echo e($b['business_name']); ?>">
            <?php else: ?>
                <div class="biz-logo-ph"><div class="ph" style="--hue:<?php echo category_hue($cat); ?>"><?php echo category_icon($cat, 36); ?></div></div>
            <?php endif; ?>
            <div>
                <h1><?php echo e($b['business_name']); ?> <?php if (!empty($b['is_verified'])) echo verified_badge(); ?></h1>
                <div class="biz-sub">
                    <span><?php echo e($cat); ?> · <?php echo e(biz_city($b)); ?></span>
                    <span><?php if ((int)$b['review_count'] > 0): ?><span class="star">★</span> <strong style="color:var(--ink)"><?php echo number_format((float)$b['avg_rating'], 1); ?></strong> (<?php echo number_format((int)$b['review_count']); ?> reviews)<?php else: ?>No reviews yet<?php endif; ?></span>
                    <?php if ($st): ?><span style="color:<?php echo $st['open'] ? 'var(--green)' : 'var(--red)'; ?>;font-weight:600"><?php echo $st['open'] ? 'Open now' : 'Closed'; ?></span><span><?php echo $st['open'] ? 'Closes ' . e($st['closes']) : 'Opens ' . e($st['opens']); ?></span><?php endif; ?>
                    <span><?php echo icon('eye', 14); ?> <?php echo (int)$b['views']; ?> views</span>
                </div>
            </div>
        </div>
        <div class="action-row">
            <?php if ($tel): ?><a class="btn" href="tel:<?php echo e($tel); ?>" data-track="call" data-biz="<?php echo $id; ?>"><?php echo icon('phone', 16); ?> Call</a><?php endif; ?>
            <?php if ($wa): ?><a class="btn btn-green" target="_blank" rel="noopener" href="https://wa.me/<?php echo e($wa); ?>" data-track="whatsapp" data-biz="<?php echo $id; ?>"><?php echo icon('chat', 16); ?> WhatsApp</a><?php endif; ?>
            <?php if (!empty($b['email'])): ?><a class="btn btn-light" href="mailto:<?php echo e($b['email']); ?>" data-track="email" data-biz="<?php echo $id; ?>"><?php echo icon('mail', 16); ?> Email</a><?php endif; ?>
            <?php if ($site): ?><a class="btn btn-light" target="_blank" rel="noopener" href="<?php echo e($site); ?>" data-track="website" data-biz="<?php echo $id; ?>"><?php echo icon('globe', 16); ?> Website</a><?php endif; ?>
            <?php if ($isOwner): ?><a class="btn btn-light" href="<?php echo e(url('business/business_dashboard.php?tab=listing')); ?>">Edit listing</a><?php endif; ?>
        </div>
    </div>

    <div class="tabs-bar">
        <a href="#overview" class="on">Overview</a><a href="#services">Services</a>
        <a href="#reviews">Reviews (<?php echo count($reviews); ?>)</a><a href="#photos">Photos</a><a href="#location">Location</a>
    </div>

    <div class="biz-main">
        <main>
            <section id="overview" class="panel">
                <h2>About</h2>
                <p><?php echo nl2br(e($b['description'] ?: 'This business has not added a description yet.')); ?></p>
                <?php if ($services): ?><div class="chips"><?php foreach (array_slice($services, 0, 8) as $sv): ?><span class="chip"><?php echo e($sv['service_name']); ?></span><?php endforeach; ?></div><?php endif; ?>
            </section>

            <section id="services" class="panel">
                <h2>Services</h2>
                <?php if ($services): ?>
                    <div class="service-list"><?php foreach ($services as $sv): ?>
                        <div class="service-item"><?php echo icon('check', 16); ?><span><?php echo e($sv['service_name']); ?></span><?php if (!empty($sv['price'])): ?><span class="price"><?php echo e($sv['price']); ?></span><?php endif; ?></div>
                    <?php endforeach; ?></div>
                    <p class="muted" style="margin:12px 0 0;font-size:13px">Prices are a guide. Contact the business to confirm.</p>
                <?php else: ?><p class="muted">This business has not listed its services yet. Contact them to ask what they offer.</p><?php endif; ?>
            </section>

            <section id="reviews" class="panel">
                <h2>Reviews (<?php echo count($reviews); ?>)</h2>
                <?php if (!is_logged_in()): ?>
                    <p><a href="<?php echo e(url('login.php')); ?>">Log in</a> to leave a review.</p>
                <?php elseif ($isOwner): ?><p class="muted">You can't review your own business.</p>
                <?php elseif ($myReview): ?><p class="muted">You have already reviewed this business. Thank you!</p>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(url('add_review.php')); ?>" style="margin-bottom:20px">
                        <?php echo csrf_field(); ?><input type="hidden" name="business_id" value="<?php echo $id; ?>">
                        <div class="field"><label for="rating">Your rating</label>
                            <select name="rating" id="rating" required>
                                <option value="5">★★★★★ Excellent</option><option value="4">★★★★☆ Very good</option>
                                <option value="3">★★★☆☆ Average</option><option value="2">★★☆☆☆ Poor</option><option value="1">★☆☆☆☆ Terrible</option>
                            </select></div>
                        <div class="field"><label for="comment">Your review</label>
                            <textarea name="comment" id="comment" placeholder="Share your experience..." minlength="5" maxlength="1000" required></textarea></div>
                        <button type="submit">Submit review</button>
                    </form>
                <?php endif; ?>

                <?php if ($reviews): foreach ($reviews as $r): ?>
                    <div class="review">
                        <div class="review-head"><div><span class="avatar"><?php echo e(strtoupper(substr($r['name'], 0, 1))); ?></span><strong><?php echo e($r['name']); ?></strong></div><span class="stars"><?php echo stars($r['rating']); ?></span></div>
                        <p style="margin:8px 0 6px"><?php echo nl2br(e($r['comment'])); ?></p>
                        <small class="muted"><?php echo e(date('d M Y', strtotime($r['created_at']))); ?></small>
                        <?php if ($uid && ((int)$r['user_id'] === $uid || current_role() === 'admin')): ?>
                            <form method="POST" action="<?php echo e(url('delete_review.php')); ?>" class="inline-form" onsubmit="return confirm('Delete this review?');">
                                <?php echo csrf_field(); ?><input type="hidden" name="review_id" value="<?php echo (int)$r['id']; ?>"><input type="hidden" name="business_id" value="<?php echo $id; ?>">
                                <button type="submit" class="btn btn-danger btn-sm" style="margin-left:10px">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; else: ?><p class="muted">No reviews yet. Be the first to share your experience.</p><?php endif; ?>
            </section>

            <section id="photos" class="panel">
                <h2>Photos</h2>
                <?php if ($gallery): ?><div class="gallery"><?php foreach ($gallery as $img): ?>
                    <a href="<?php echo e(upload_url($img['image'])); ?>" target="_blank" rel="noopener"><img src="<?php echo e(upload_url($img['image'])); ?>" alt="Photo of <?php echo e($b['business_name']); ?>" loading="lazy"></a>
                <?php endforeach; ?></div><?php else: ?><p class="muted">No photos uploaded yet.</p><?php endif; ?>
            </section>
        </main>

        <aside>
            <div id="location" class="map-card" style="scroll-margin-top:90px">
                <div class="map-art"><svg viewBox="0 0 340 140" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                    <rect width="340" height="140" fill="#E8EFFA"/><path d="M-10 100 L120 70 L220 90 L350 40" stroke="#fff" stroke-width="10" fill="none"/>
                    <path d="M60 -10 L90 150" stroke="#fff" stroke-width="8"/><path d="M240 -10 L200 150" stroke="#fff" stroke-width="8"/>
                    <path d="M-10 40 L350 70" stroke="#D5E2F7" stroke-width="5"/><rect x="250" y="95" width="60" height="35" rx="6" fill="#CFE0F7"/><rect x="20" y="10" width="50" height="30" rx="6" fill="#CFE0F7"/>
                    <g transform="translate(160 36)"><path d="M10 0C4.5 0 0 4.4 0 9.8 0 17 10 28 10 28S20 17 20 9.8C20 4.400 15.500 0 10 0z" fill="#2563EB"/><circle cx="10" cy="9.500" r="4" fill="#fff"/></g></svg></div>
                <div class="mc-body"><strong><?php echo e($addr); ?></strong>
                    <a class="btn btn-light btn-sm mt" style="margin-top:12px" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($b['business_name'] . ', ' . $addr); ?>"><?php echo icon('pin', 15); ?> Get Directions</a></div>
            </div>

            <div class="panel" style="margin-top:18px">
                <h3>Contact</h3>
                <div class="contact-list">
                    <?php if (!empty($b['phone'])): ?><div><?php echo icon('phone', 16); ?><?php echo e($b['phone']); ?></div><?php endif; ?>
                    <?php if (!empty($b['email'])): ?><div><?php echo icon('mail', 16); ?><?php echo e($b['email']); ?></div><?php endif; ?>
                    <?php if ($site): ?><div><?php echo icon('globe', 16); ?><?php echo e(preg_replace('#^https?://#i', '', $site)); ?></div><?php endif; ?>
                    <div><?php echo icon('pin', 16); ?><?php echo e($addr); ?></div>
                </div>
            </div>

            <div class="panel" style="margin-top:18px">
                <h3>Opening Hours</h3>
                <?php if ($st): ?>
                    <div class="hours">
                        <div class="row"><span><?php echo e(days_label($b['open_days'])); ?></span><strong><?php echo e($st['opens']); ?> – <?php echo e($st['closes']); ?></strong></div>
                        <div class="row"><span>Right now</span><?php echo open_pill($b); ?></div>
                    </div>
                <?php else: ?><p class="muted">Opening hours not provided. Please call to confirm.</p><?php endif; ?>
            </div>
        </aside>
    </div>

    <?php if ($similar): ?>
        <div class="section-head"><h2>More in <?php echo e($cat); ?></h2><a href="<?php echo e(url('search.php?category[]=' . (int)$b['category_id'])); ?>">View all →</a></div>
        <div class="grid-3" style="margin-bottom:50px"><?php foreach ($similar as $s) business_card($s); ?></div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
