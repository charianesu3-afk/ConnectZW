<?php
require_once __DIR__ . '/includes/config.php';

$q        = trim($_GET['search'] ?? '');
$catIds   = array_values(array_filter(array_map('intval', (array)($_GET['category'] ?? []))));
$province = trim($_GET['province'] ?? '');
$city     = trim($_GET['city'] ?? '');
$sort     = $_GET['sort'] ?? 'popular';
$view     = ($_GET['view'] ?? 'list') === 'grid' ? 'grid' : 'list';
$verified = !empty($_GET['verified']);
$openNow  = !empty($_GET['open']);

$where = []; $types = ''; $params = [];
if ($q !== '') {
    $where[] = '(b.business_name LIKE ? OR b.description LIKE ? OR c.category_name LIKE ?
                 OR EXISTS (SELECT 1 FROM business_services s WHERE s.business_id = b.id AND s.service_name LIKE ?))';
    $like = '%' . $q . '%'; $types .= 'ssss';
    array_push($params, $like, $like, $like, $like);
}
if ($catIds) {
    $where[] = 'b.category_id IN (' . implode(',', array_fill(0, count($catIds), '?')) . ')';
    $types .= str_repeat('i', count($catIds));
    $params = array_merge($params, $catIds);
}
if ($province !== '') { $where[] = 'b.province = ?'; $types .= 's'; $params[] = $province; }
if ($city !== '')     { $where[] = 'b.city = ?';     $types .= 's'; $params[] = $city; }
if ($verified)        { $where[] = 'b.is_verified = 1'; }
if ($openNow) {
    $t = date('H:i:s'); $dow = (int)date('N');
    $where[] = "(b.opens IS NOT NULL AND b.closes IS NOT NULL AND
        ((b.opens <= b.closes AND ? BETWEEN b.opens AND b.closes) OR (b.opens > b.closes AND (? >= b.opens OR ? <= b.closes)))
        AND (b.open_days = 'daily' OR (b.open_days = 'mon-sat' AND ? <= 6) OR (b.open_days = 'mon-fri' AND ? <= 5)))";
    $types .= 'sssii';
    array_push($params, $t, $t, $t, $dow, $dow);
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$orders = ['popular' => 'b.views DESC', 'rating' => 'avg_rating DESC, review_count DESC', 'new' => 'b.created_at DESC', 'name' => 'b.business_name ASC'];
$orderSql = ' ORDER BY ' . ($orders[$sort] ?? $orders['popular']);

$perPage = 12;
$total = (int)db_one("SELECT COUNT(*) n FROM businesses b LEFT JOIN categories c ON b.category_id = c.id" . $whereSql, $types, $params)['n'];
$pages = max(1, (int)ceil($total / $perPage));
$page  = min(max(1, (int)($_GET['page'] ?? 1)), $pages);
$offset = ($page - 1) * $perPage;
$rows  = db_all(BUSINESS_SELECT . $whereSql . $orderSql . ' LIMIT ? OFFSET ?', $types . 'ii', array_merge($params, [$perPage, $offset]));

$cats = db_all("SELECT c.id, c.category_name, COUNT(b.id) AS total FROM categories c
                LEFT JOIN businesses b ON b.category_id = c.id GROUP BY c.id, c.category_name ORDER BY c.category_name");

function page_link($overrides) {
    $qs = array_merge($_GET, $overrides);
    return e(url('search.php?' . http_build_query($qs)));
}

$pageTitle = "Find Businesses | ConnectZW";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="page-hero">
    <div class="container"><h1>Businesses</h1><p>Search services, businesses and professionals around Zimbabwe.</p></div>
</div>

<div class="container list-layout">

    <aside>
        <form method="GET" class="filters">
            <div class="f-head"><h3>Filters</h3><a class="clear" href="<?php echo e(url('search.php')); ?>">Clear all</a></div>
            <input type="hidden" name="view" value="<?php echo e($view); ?>">

            <div class="fg"><label for="search">Search keyword</label>
                <input type="text" id="search" name="search" placeholder="Search businesses..." value="<?php echo e($q); ?>"></div>

            <div class="fg"><label>Category</label>
                <div class="checks">
                    <?php foreach ($cats as $c): ?>
                        <label><input type="checkbox" name="category[]" value="<?php echo (int)$c['id']; ?>" <?php echo in_array((int)$c['id'], $catIds, true) ? 'checked' : ''; ?>>
                            <?php echo e($c['category_name']); ?><span><?php echo (int)$c['total']; ?></span></label>
                    <?php endforeach; ?>
                </div></div>

            <div class="fg"><label for="province">Location</label>
                <select name="province" id="province" data-province-select="city">
                    <option value="">All Zimbabwe</option>
                    <?php foreach (zw_provinces() as $p): ?><option value="<?php echo e($p); ?>" <?php echo $province === $p ? 'selected' : ''; ?>><?php echo e($p); ?></option><?php endforeach; ?>
                </select></div>

            <div class="fg"><label for="city">City or Town</label>
                <select name="city" id="city" data-current="<?php echo e($city); ?>">
                    <option value="">Select city</option>
                    <?php foreach (zw_cities() as $cn => $pv): ?><option value="<?php echo e($cn); ?>" data-prov="<?php echo e($pv); ?>" <?php echo $city === $cn ? 'selected' : ''; ?>><?php echo e($cn); ?></option><?php endforeach; ?>
                </select></div>

            <div class="fg"><label for="sort">Sort by</label>
                <select name="sort" id="sort">
                    <?php foreach (['popular' => 'Most Popular', 'rating' => 'Top Rated', 'new' => 'Newest', 'name' => 'Name (A–Z)'] as $k => $l): ?>
                        <option value="<?php echo $k; ?>" <?php echo $sort === $k ? 'selected' : ''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select></div>

            <div class="fg checks" style="max-height:none">
                <label><input type="checkbox" name="verified" value="1" <?php echo $verified ? 'checked' : ''; ?>> Verified only</label>
                <label><input type="checkbox" name="open" value="1" <?php echo $openNow ? 'checked' : ''; ?>> Open now</label>
            </div>

            <button type="submit" class="btn btn-block">Apply Filters</button>
        </form>
    </aside>

    <main>
        <div class="list-top">
            <span class="muted">Showing <?php echo $total ? ($offset + 1) : 0; ?> - <?php echo min($offset + $perPage, $total); ?> of <?php echo number_format($total); ?> businesses</span>
            <div class="view-toggle">
                <a class="<?php echo $view === 'grid' ? 'on' : ''; ?>" href="<?php echo page_link(['view' => 'grid', 'page' => 1]); ?>"><?php echo icon('grid', 15); ?> Grid</a>
                <a class="<?php echo $view === 'list' ? 'on' : ''; ?>" href="<?php echo page_link(['view' => 'list', 'page' => 1]); ?>"><?php echo icon('menu', 15); ?> List</a>
            </div>
        </div>

        <?php if ($rows): ?>
            <?php if ($view === 'grid'): ?>
                <div class="grid-3"><?php foreach ($rows as $b) business_card($b); ?></div>
            <?php else: ?>
                <div class="rows"><?php foreach ($rows as $b) business_row($b); ?></div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-box"><?php echo icon('search', 40); ?><h3>No businesses found</h3>
                <p class="muted">Try another search, location or category.</p>
                <a class="btn" href="<?php echo e(url('search.php')); ?>">Reset filters</a></div>
        <?php endif; ?>

        <?php if ($pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?><a href="<?php echo page_link(['page' => $page - 1]); ?>">← Prev</a><?php endif; ?>
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <?php if ($i === $page): ?><span class="current"><?php echo $i; ?></span><?php else: ?><a href="<?php echo page_link(['page' => $i]); ?>"><?php echo $i; ?></a><?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $pages): ?><a href="<?php echo page_link(['page' => $page + 1]); ?>">Next →</a><?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include 'includes/footer.php'; ?>
