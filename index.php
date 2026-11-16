<?php
require_once __DIR__ . '/includes/config.php';

$topCats = db_all("SELECT c.id, c.category_name, COUNT(b.id) AS total
    FROM categories c LEFT JOIN businesses b ON b.category_id = c.id
    GROUP BY c.id, c.category_name ORDER BY total DESC, c.category_name ASC LIMIT 6");

$featured = db_all(BUSINESS_SELECT . " ORDER BY b.is_verified DESC, review_count DESC, b.views DESC LIMIT 4");
$heroImg  = asset_image('hero.jpg');

$pageTitle = "ConnectZW | Discover. Connect. Grow. Zimbabwean Businesses.";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="hero" <?php echo $heroImg ? 'style="--hero-img:url(\'' . e($heroImg) . '\')"' : ''; ?>>
    <div class="container">
        <span class="chip-top">Proudly Zimbabwean <?php echo flag_zw(18); ?></span>
        <h1>Discover. Connect. Grow. Zimbabwean Businesses.</h1>
        <p class="lead">Find trusted businesses and services across Zimbabwe. Support local. Build Zimbabwe.</p>

        <form action="<?php echo e(url('search.php')); ?>" method="GET" class="search-box">
            <label class="sb-field"><?php echo icon('search', 18); ?>
                <input type="text" name="search" placeholder="What are you looking for?" aria-label="What are you looking for?">
            </label>
            <label class="sb-field sb-loc"><?php echo icon('pin', 18); ?>
                <select name="province" aria-label="Location">
                    <option value="">All Zimbabwe</option>
                    <?php foreach (zw_provinces() as $p): ?><option value="<?php echo e($p); ?>"><?php echo e($p); ?></option><?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="btn">Search</button>
        </form>

        <div class="popular">Popular searches:
            <?php foreach (['Mechanics', 'Restaurants', 'Salons', 'Plumbers', 'Shops'] as $s): ?>
                <a href="<?php echo e(url('search.php?search=' . urlencode($s))); ?>"><?php echo e($s); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="trust">
    <div class="container trust-grid">
        <div class="trust-item"><div class="ti"><?php echo icon('shield', 22); ?></div><div><strong>Verified Businesses</strong><span>Quality you can trust</span></div></div>
        <div class="trust-item"><div class="ti"><?php echo icon('users', 22); ?></div><div><strong>Local Support</strong><span>We are here for you</span></div></div>
        <div class="trust-item"><div class="ti"><?php echo icon('phone', 22); ?></div><div><strong>Easy to Connect</strong><span>Call, WhatsApp, Email</span></div></div>
        <div class="trust-item"><div class="ti"><?php echo flag_zw(22); ?></div><div><strong>100% Zimbabwe</strong><span>For Zimbabweans</span></div></div>
    </div>
</div>

<section class="reveal">
    <div class="container">
        <div class="section-head"><h2>Browse Categories</h2><a href="<?php echo e(url('categories.php')); ?>">View all categories →</a></div>
        <div class="cat-tiles">
            <?php foreach ($topCats as $c): ?>
                <a class="cat-tile" href="<?php echo e(url('search.php?category[]=' . (int)$c['id'])); ?>">
                    <?php echo category_icon($c['category_name'], 30); ?>
                    <strong><?php echo e($c['category_name']); ?></strong>
                    <small><?php echo (int)$c['total']; ?> <?php echo $c['total'] == 1 ? 'business' : 'businesses'; ?></small>
                </a>
            <?php endforeach; ?>
            <a class="cat-tile" href="<?php echo e(url('categories.php')); ?>">
                <?php echo icon('dots', 30); ?><strong>More Categories</strong><small>Explore more</small>
            </a>
        </div>
    </div>
</section>

<section class="reveal" style="padding-top:10px">
    <div class="container">
        <div class="section-head"><h2>Featured Businesses</h2><a href="<?php echo e(url('search.php')); ?>">View all businesses →</a></div>
        <div class="grid-4">
            <?php if ($featured): foreach ($featured as $b) business_card($b); else: ?>
                <div class="empty-box" style="grid-column:1/-1">
                    <?php echo icon('store', 40); ?><h3>No businesses yet</h3>
                    <p class="muted">Be the first to list your business on ConnectZW.</p>
                    <a class="btn" href="<?php echo e(url('register.php')); ?>">List Your Business</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="reveal" style="padding-top:10px">
    <div class="container two-col">
        <div class="cta-card">
            <div>
                <h3>List Your Business Today</h3>
                <p>Get discovered by thousands of potential customers across Zimbabwe.</p>
                <a class="btn" href="<?php echo e(url('register.php')); ?>">List Your Business</a>
            </div>
            <div class="cta-art"><?php echo icon('store', 110); ?></div>
        </div>
        <div class="why-card">
            <h3>Why ConnectZW?</h3>
            <ul class="checklist">
                <?php foreach (['Boost your visibility', 'Connect with local customers', 'Grow your business', '100% Zimbabwean platform'] as $w): ?>
                    <li><span class="ck"><?php echo icon('check', 14); ?></span><?php echo e($w); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="reveal">
    <div class="container">
        <div class="map-panel">
            <div>
                <h2>Businesses across Zimbabwe</h2>
                <p>From Harare and Bulawayo to Mutare, Gweru, Masvingo and Victoria Falls. ConnectZW links customers with trusted local businesses nationwide.</p>
                <a class="btn" href="<?php echo e(url('search.php')); ?>">Explore businesses</a>
            </div>
            <div><?php include __DIR__ . '/includes/map.php'; ?></div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
