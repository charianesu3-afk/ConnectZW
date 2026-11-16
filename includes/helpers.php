<?php
/* ---------- basics ---------- */
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function url($path = '') { return BASE_URL . '/' . ltrim($path, '/'); }
function redirect($path) { header('Location: ' . url($path)); exit(); }

/* ---------- flash messages ---------- */
function flash($type, $msg) { $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg]; }
function flash_render() {
    if (empty($_SESSION['flash'])) return;
    echo '<div class="container flash-wrap">';
    foreach ($_SESSION['flash'] as $f) echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['msg']) . '</div>';
    echo '</div>';
    unset($_SESSION['flash']);
}

/* ---------- CSRF ---------- */
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check() {
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        http_response_code(403);
        die('Invalid request. Please go back, refresh the page and try again.');
    }
}
function require_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
    csrf_check();
}

/* ---------- auth ---------- */
function is_logged_in() { return !empty($_SESSION['user_id']); }
function current_role() { return $_SESSION['role'] ?? null; }
function require_login() {
    if (!is_logged_in()) { flash('error', 'Please log in to continue.'); redirect('login.php'); }
}
function require_role($roles) {
    require_login();
    if (!in_array(current_role(), (array)$roles, true)) { http_response_code(403); die('You do not have permission to view this page.'); }
}
function dashboard_url() {
    switch (current_role()) {
        case 'admin':    return url('admin/dashboard.php');
        case 'business': return url('business/business_dashboard.php');
        default:         return url('customer/dashboard.php');
    }
}

/* ---------- database (prepared statements) ---------- */
function db_query($sql, $types = '', $params = []) {
    global $conn;
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) die('Database error: ' . e(mysqli_error($conn)));
    if ($types !== '') mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    return $stmt;
}
function db_all($sql, $types = '', $params = []) {
    $stmt = db_query($sql, $types, $params);
    $res = mysqli_stmt_get_result($stmt);
    $rows = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    mysqli_stmt_close($stmt);
    return $rows;
}
function db_one($sql, $types = '', $params = []) { $r = db_all($sql, $types, $params); return $r[0] ?? null; }
function db_exec($sql, $types = '', $params = []) {
    $stmt = db_query($sql, $types, $params);
    $n = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $n;
}
function db_last_id() { global $conn; return (int)mysqli_insert_id($conn); }

/* ---------- Zimbabwe places ---------- */
function zw_cities() {
    return ['Harare'=>'Harare','Chitungwiza'=>'Harare','Bulawayo'=>'Bulawayo',
        'Mutare'=>'Manicaland','Rusape'=>'Manicaland','Chipinge'=>'Manicaland',
        'Gweru'=>'Midlands','Kwekwe'=>'Midlands','Redcliff'=>'Midlands','Zvishavane'=>'Midlands','Shurugwi'=>'Midlands',
        'Masvingo'=>'Masvingo','Chiredzi'=>'Masvingo',
        'Chinhoyi'=>'Mashonaland West','Kadoma'=>'Mashonaland West','Kariba'=>'Mashonaland West','Chegutu'=>'Mashonaland West','Karoi'=>'Mashonaland West',
        'Bindura'=>'Mashonaland Central','Mt Darwin'=>'Mashonaland Central',
        'Marondera'=>'Mashonaland East',
        'Victoria Falls'=>'Matabeleland North','Hwange'=>'Matabeleland North','Lupane'=>'Matabeleland North',
        'Beitbridge'=>'Matabeleland South','Gwanda'=>'Matabeleland South','Plumtree'=>'Matabeleland South'];
}
function zw_provinces() { return array_values(array_unique(array_values(zw_cities()))); }
function province_of($city) { return zw_cities()[$city] ?? null; }

/* ---------- display helpers ---------- */
function category_slug($name) {
    $s = strtolower(str_replace('&', 'and', (string)$name));
    return trim(preg_replace('/[^a-z0-9]+/', '-', $s), '-');
}
function category_icon_name($name) {
    $n = strtolower($name);
    $rules = [
        'car' => ['auto', 'mechanic', 'tyre', 'wheel', 'car wash', 'garage'],
        'utensils' => ['restaurant', 'cafe', 'food', 'bakeri', 'catering'],
        'bag' => ['grocer', 'supermarket', 'shop', 'cloth', 'fashion', 'jewel', 'tailor', 'print', 'laundry'],
        'smile' => ['beauty', 'salon', 'barber', 'spa'],
        'plus-circle' => ['health', 'clinic', 'pharm', 'vet', 'gym', 'fitness'],
        'hardhat' => ['construct', 'build', 'hardware', 'architect', 'real estate', 'weld', 'carpent', 'furniture'],
        'tool' => ['plumb', 'electric', 'appliance', 'solar', 'clean', 'garden', 'security'],
        'briefcase' => ['account', 'legal', 'insur', 'financ'],
        'book' => ['school', 'tutor', 'driving'],
        'monitor' => ['computer', 'phone', 'tech', 'photo'],
        'truck' => ['transport', 'taxi', 'courier', 'deliver'],
        'globe' => ['travel', 'tour'],
        'home' => ['hotel', 'lodge', 'home'],
        'leaf' => ['agri', 'farm'],
        'users' => ['event', 'funeral'],
    ];
    foreach ($rules as $icon => $words) foreach ($words as $w) if (strpos($n, $w) !== false) return $icon;
    return 'store';
}
function category_icon($name, $size = 28) { return icon(category_icon_name($name), $size); }
function category_hue($name) { return abs(crc32((string)$name)) % 360; }
function stars($avg) { $r = max(0, min(5, (int)round((float)$avg))); return str_repeat('★', $r) . str_repeat('☆', 5 - $r); }
function wa_number($phone) {
    $d = preg_replace('/\D+/', '', (string)$phone);
    if ($d === '') return '';
    if (strpos($d, '263') === 0) return $d;
    if ($d[0] === '0') return '263' . substr($d, 1);
    return '263' . $d;
}
function upload_url($file) { return url('uploads/' . rawurlencode($file)); }
function asset_image($file) {
    $path = __DIR__ . '/../assets/images/' . $file;
    return is_file($path) ? url('assets/images/' . implode('/', array_map('rawurlencode', explode('/', $file)))) : null;
}
function short_num($n) { return $n >= 1000 ? number_format($n / 1000, 1) . 'k' : (string)$n; }
function pct_change($now, $before) {
    if ($before <= 0) return $now > 0 ? 100 : 0;
    return (int)round(($now - $before) * 100 / $before);
}
function trend_html($pct, $suffix = 'this month') {
    $up = $pct >= 0;
    return '<small class="trend ' . ($up ? 'up' : 'down') . '">' . ($up ? '↑ ' : '↓ ') . abs($pct) . '% ' . e($suffix) . '</small>';
}

/* Real category photos: assets/images/categories/<slug>.jpg, or the photo names from the first ConnectZW project */
function category_image($name) {
    static $cache = [];
    if (array_key_exists($name, $cache)) return $cache[$name];
    $slug = category_slug($name);
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        if ($u = asset_image('categories/' . $slug . '.' . $ext)) return $cache[$name] = $u;
    }
    $legacy = ['accounting & bookkeeping'=>'accounting.jpg','agricultural supplies'=>'agricultural-supplies.jpg',
        'agriculture & farming'=>'agriculture.jpg','appliance repairs'=>'appliance-repairs.jpg',
        'auto electrical services'=>'auto-electrical.jpg','auto parts'=>'auto-parts.jpg',
        'automotive & mechanics'=>'mechanics.jpg','bakeries & confectionery'=>'bakery.jpg',
        'electricians'=>'electrician.jpg','electrician'=>'electrician.jpg','supermarkets & groceries'=>'grocery.jpg',
        'plumbers'=>'plumbers.jpg','restaurants & cafes'=>'restaurants.jpg','courier & delivery'=>'transport and delivery.jpg',
        'barbers & salons'=>'saloon.jpg','jewellery & accessories'=>'jewellery.jpg'];
    $key = strtolower(trim((string)$name));
    if (isset($legacy[$key]) && ($u = asset_image($legacy[$key]))) return $cache[$name] = $u;
    return $cache[$name] = null;
}

/* ---------- opening hours ---------- */
function open_status($b) {
    if (empty($b['opens']) || empty($b['closes'])) return null;
    $now = new DateTime('now', new DateTimeZone('Africa/Harare'));
    $dow = (int)$now->format('N');
    $days = $b['open_days'] ?? 'daily';
    $dayOk = $days === 'daily' || ($days === 'mon-sat' && $dow <= 6) || ($days === 'mon-fri' && $dow <= 5);
    $t = $now->format('H:i:s'); $o = $b['opens']; $c = $b['closes'];
    $in = $o <= $c ? ($t >= $o && $t <= $c) : ($t >= $o || $t <= $c);
    return ['open' => ($dayOk && $in), 'closes' => substr($c, 0, 5), 'opens' => substr($o, 0, 5)];
}
function days_label($d) { return ['daily' => 'Every day', 'mon-sat' => 'Monday to Saturday', 'mon-fri' => 'Monday to Friday'][$d] ?? 'Every day'; }
function open_pill($b) {
    $s = open_status($b);
    if (!$s) return '';
    return $s['open'] ? '<span class="pill open">Open now</span>' : '<span class="pill closed">Closed</span>';
}

/* ---------- stats tracking ---------- */
function track_stat($bid, $field) {
    if (!in_array($field, ['views', 'calls', 'whatsapp', 'emails', 'websites'], true)) return;
    db_exec("INSERT INTO business_stats (business_id, day, $field) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE $field = $field + 1",
            'is', [(int)$bid, date('Y-m-d')]);
}
function stat_sum($bid, $fields, $from, $to) {
    $expr = implode(' + ', array_map(fn($f) => "COALESCE(SUM($f),0)", $fields));
    return (int)db_one("SELECT ($expr) AS n FROM business_stats WHERE business_id = ? AND day BETWEEN ? AND ?", 'iss', [(int)$bid, $from, $to])['n'];
}

/* ---------- image uploads ---------- */
function upload_image($file, &$error) {
    $error = '';
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) { $error = 'Upload failed. Try a smaller image.'; return null; }
    if ($file['size'] > 3 * 1024 * 1024) { $error = 'Each image must be under 3MB.'; return null; }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) { $error = 'Only JPG, PNG or WebP images are allowed.'; return null; }
    $dir = __DIR__ . '/../uploads/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) { $error = 'Could not save the image.'; return null; }
    return $name;
}
function delete_upload($name) {
    if (!$name) return;
    $p = __DIR__ . '/../uploads/' . basename($name);
    if (is_file($p)) @unlink($p);
}

/* ---------- business listing query + cards ---------- */
const BUSINESS_SELECT = "SELECT b.*, c.category_name,
    (SELECT AVG(r.rating) FROM reviews r WHERE r.business_id = b.id) AS avg_rating,
    (SELECT COUNT(*) FROM reviews r WHERE r.business_id = b.id) AS review_count,
    (SELECT GROUP_CONCAT(s.service_name ORDER BY s.id SEPARATOR '|') FROM business_services s WHERE s.business_id = b.id) AS services,
    (SELECT bi.image FROM business_images bi WHERE bi.business_id = b.id ORDER BY bi.id ASC LIMIT 1) AS cover
    FROM businesses b LEFT JOIN categories c ON b.category_id = c.id ";

function biz_photo($b) {
    if (!empty($b['cover'])) return upload_url($b['cover']);
    return category_image($b['category_name'] ?? '');
}
function biz_city($b) { return $b['city'] ?: trim(explode(',', (string)$b['location'])[0]); }
function biz_photo_html($b, $size = 54) {
    $photo = biz_photo($b); $cat = $b['category_name'] ?? 'Business';
    if ($photo) return '<img src="' . e($photo) . '" alt="' . e($b['business_name']) . '" loading="lazy">';
    return '<div class="ph" style="--hue:' . category_hue($cat) . '">' . category_icon($cat, $size) . '</div>';
}
function verified_badge() { return '<span class="verified" title="Verified business">' . icon('check-circle', 14) . ' Verified</span>'; }

function business_card($b) {
    $cnt = (int)($b['review_count'] ?? 0);
    $cat = $b['category_name'] ?? 'Business';
    $link = e(url('business_profile.php?id=' . (int)$b['id']));
    ?>
    <article class="bcard">
        <a class="bphoto" href="<?php echo $link; ?>"><?php echo biz_photo_html($b, 54); ?></a>
        <div class="bbody">
            <h3><a href="<?php echo $link; ?>"><?php echo e($b['business_name']); ?></a><?php if (!empty($b['is_verified'])) echo ' <span class="vdot" title="Verified">' . icon('check-circle', 15) . '</span>'; ?></h3>
            <p class="bmeta"><?php echo e($cat); ?> · <?php echo e(biz_city($b)); ?></p>
            <p class="brate"><?php if ($cnt > 0): ?><span class="star">★</span> <strong><?php echo number_format((float)$b['avg_rating'], 1); ?></strong> <span class="muted">(<?php echo $cnt; ?>)</span><?php else: ?><span class="muted">No reviews yet</span><?php endif; ?></p>
            <?php if (!empty($b['phone'])): ?><p class="bphone"><?php echo icon('phone', 14); ?> <?php echo e($b['phone']); ?></p><?php endif; ?>
            <?php echo open_pill($b); ?>
        </div>
    </article>
    <?php
}

function business_row($b) {
    $cnt = (int)($b['review_count'] ?? 0);
    $cat = $b['category_name'] ?? 'Business';
    $link = e(url('business_profile.php?id=' . (int)$b['id']));
    $svc = !empty($b['services']) ? array_slice(explode('|', $b['services']), 0, 3) : [];
    ?>
    <article class="brow">
        <a class="bphoto" href="<?php echo $link; ?>"><?php echo biz_photo_html($b, 40); ?></a>
        <div class="brow-main">
            <h3><a href="<?php echo $link; ?>"><?php echo e($b['business_name']); ?></a><?php if (!empty($b['is_verified'])) echo ' <span class="vdot" title="Verified">' . icon('check-circle', 15) . '</span>'; ?></h3>
            <p class="bmeta"><?php echo e($cat); ?> · <?php echo e(biz_city($b)); ?></p>
            <p class="brate"><?php if ($cnt > 0): ?><span class="star">★</span> <strong><?php echo number_format((float)$b['avg_rating'], 1); ?></strong> <span class="muted">(<?php echo $cnt; ?>)</span><?php else: ?><span class="muted">No reviews yet</span><?php endif; ?></p>
            <?php if ($svc): ?><div class="chips"><?php foreach ($svc as $s): ?><span class="chip"><?php echo e($s); ?></span><?php endforeach; ?></div><?php endif; ?>
        </div>
        <div class="brow-side">
            <?php echo open_pill($b); ?>
            <?php if (!empty($b['phone'])): ?><span class="bphone"><?php echo icon('phone', 14); ?> <?php echo e($b['phone']); ?></span><?php endif; ?>
            <a class="btn btn-sm btn-light" href="<?php echo $link; ?>">View</a>
        </div>
    </article>
    <?php
}

/* ---------- misc ---------- */
function time_ago($ts) {
    $d = time() - (is_numeric($ts) ? $ts : strtotime($ts));
    if ($d < 60) return 'just now';
    if ($d < 3600) return floor($d / 60) . ' mins ago';
    if ($d < 86400) return floor($d / 3600) . ' hour' . (floor($d / 3600) > 1 ? 's' : '') . ' ago';
    if ($d < 86400 * 30) return floor($d / 86400) . ' day' . (floor($d / 86400) > 1 ? 's' : '') . ' ago';
    return date('d M Y', is_numeric($ts) ? $ts : strtotime($ts));
}
function slugify($s) { return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-') ?: 'post'; }
function render_post($text) {
    $out = '';
    foreach (preg_split('/\n\s*\n/', trim((string)$text)) as $block) {
        $block = trim($block);
        if ($block === '') continue;
        if (strpos($block, '## ') === 0) $out .= '<h2>' . e(substr($block, 3)) . '</h2>';
        else $out .= '<p>' . nl2br(e($block)) . '</p>';
    }
    return $out;
}
