<?php
require_once __DIR__ . '/includes/config.php';

$cats = db_all("SELECT c.id, c.category_name, COUNT(b.id) AS total
    FROM categories c LEFT JOIN businesses b ON b.category_id = c.id
    GROUP BY c.id, c.category_name ORDER BY c.category_name ASC");

$pageTitle = "Categories | ConnectZW";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="page-hero">
    <div class="container"><h1>Browse Categories</h1><p>Find businesses and services by category.</p></div>
</div>

<section>
    <div class="container">
        <div style="max-width:520px;margin-bottom:24px">
            <input type="text" id="categorySearch" placeholder="Search for a category..." aria-label="Search categories">
        </div>

        <div class="cat-cards">
            <?php if ($cats): foreach ($cats as $c): $img = category_image($c['category_name']); ?>
                <a class="cat-card cat-filter" data-name="<?php echo e(strtolower($c['category_name'])); ?>" href="<?php echo e(url('search.php?category[]=' . (int)$c['id'])); ?>">
                    <div class="bphoto">
                        <?php if ($img): ?><img src="<?php echo e($img); ?>" alt="<?php echo e($c['category_name']); ?>" loading="lazy">
                        <?php else: ?><div class="ph" style="--hue:<?php echo category_hue($c['category_name']); ?>"><?php echo category_icon($c['category_name'], 46); ?></div><?php endif; ?>
                    </div>
                    <div class="cc-body"><strong><?php echo e($c['category_name']); ?></strong>
                        <small><?php echo (int)$c['total']; ?> <?php echo $c['total'] == 1 ? 'business' : 'businesses'; ?></small></div>
                </a>
            <?php endforeach; else: ?>
                <div class="empty-box"><h3>No categories yet</h3></div>
            <?php endif; ?>
        </div>

        <div id="noCategories" class="empty-box" style="display:none;margin-top:20px"><?php echo icon('search', 36); ?><h3>No categories found</h3></div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
