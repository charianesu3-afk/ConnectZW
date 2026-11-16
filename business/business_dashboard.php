<?php
require_once __DIR__ . '/../includes/config.php';
require_role('business');

$uid  = (int)$_SESSION['user_id'];
$biz  = db_one('SELECT * FROM businesses WHERE user_id = ?', 'i', [$uid]);
$cats = db_all('SELECT * FROM categories ORDER BY category_name ASC');
$errors = [];
$MAX_IMAGES = 10;
$tabs = ['overview' => 'Overview', 'listing' => 'My Listing', 'services' => 'Services', 'photos' => 'Photos'];
$tab = $_GET['tab'] ?? ($biz ? 'overview' : 'listing');
if (!isset($tabs[$tab]) || (!$biz && $tab !== 'listing')) $tab = $biz ? 'overview' : 'listing';
$back = 'business/business_dashboard.php?tab=';

function clean_time($v) { return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string)$v) ? $v . ':00' : null; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $tab = 'listing';
        $name  = trim($_POST['business_name'] ?? '');
        $catId = (int)($_POST['category_id'] ?? 0);
        $city  = trim($_POST['city'] ?? '');
        $addr  = trim($_POST['location'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $site  = trim($_POST['website'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $opens = clean_time($_POST['opens'] ?? ''); $closes = clean_time($_POST['closes'] ?? '');
        $days  = in_array($_POST['open_days'] ?? '', ['daily', 'mon-sat', 'mon-fri'], true) ? $_POST['open_days'] : 'daily';
        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($name) < 2 || strlen($name) > 150) $errors[] = 'Business name must be 2 to 150 characters.';
        if (!db_one('SELECT id FROM categories WHERE id = ?', 'i', [$catId])) $errors[] = 'Please choose a category.';
        if (province_of($city) === null) $errors[] = 'Please choose your city or town.';
        if ($addr === '' || strlen($addr) > 150) $errors[] = 'Please enter your street address or area.';
        if (strlen($digits) < 9 || strlen($digits) > 15) $errors[] = 'Please enter a valid phone number.';
        if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150)) $errors[] = 'Please enter a valid email address.';
        if ($site !== '') {
            if (!preg_match('#^https?://#i', $site)) $site = 'https://' . $site;
            if (!filter_var($site, FILTER_VALIDATE_URL) || strlen($site) > 200) $errors[] = 'Please enter a valid website address.';
        }
        if (strlen($desc) > 3000) $errors[] = 'Description is too long (max 3000 characters).';

        $newLogo = upload_image($_FILES['logo'] ?? [], $upErr);
        if ($upErr) $errors[] = $upErr;

        if (!$errors) {
            $prov = province_of($city);
            $emailV = $email !== '' ? $email : null; $siteV = $site !== '' ? $site : null;
            if ($biz) {
                $logoVal = $newLogo ?? $biz['logo'];
                if ($newLogo) delete_upload($biz['logo']);
                db_exec('UPDATE businesses SET business_name=?, category_id=?, description=?, location=?, city=?, province=?, phone=?, email=?, website=?, opens=?, closes=?, open_days=?, logo=? WHERE id=? AND user_id=?',
                    'sisssssssssssii', [$name, $catId, $desc, $addr, $city, $prov, $phone, $emailV, $siteV, $opens, $closes, $days, $logoVal, $biz['id'], $uid]);
                flash('success', 'Your business has been updated.');
            } else {
                db_exec('INSERT INTO businesses (user_id, category_id, business_name, description, location, city, province, phone, email, website, opens, closes, open_days, logo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                    'iissssssssssss', [$uid, $catId, $name, $desc, $addr, $city, $prov, $phone, $emailV, $siteV, $opens, $closes, $days, $newLogo]);
                flash('success', 'Your business is now live on ConnectZW!');
            }
            redirect($back . 'overview');
        } elseif ($newLogo) { delete_upload($newLogo); }
    }

    if ($action === 'add_service' && $biz) {
        $tab = 'services';
        $sn = trim(str_replace('|', ' ', $_POST['service_name'] ?? '')); $sp = trim($_POST['price'] ?? '');
        $cnt = (int)db_one('SELECT COUNT(*) n FROM business_services WHERE business_id = ?', 'i', [$biz['id']])['n'];
        if ($sn === '' || strlen($sn) > 120) $errors[] = 'Enter a service name (max 120 characters).';
        elseif (strlen($sp) > 60) $errors[] = 'Price text is too long.';
        elseif ($cnt >= 30) $errors[] = 'You can list up to 30 services.';
        else {
            db_exec('INSERT INTO business_services (business_id, service_name, price) VALUES (?,?,?)', 'iss', [$biz['id'], $sn, $sp !== '' ? $sp : null]);
            flash('success', 'Service added.'); redirect($back . 'services');
        }
    }
    if ($action === 'delete_service' && $biz) {
        db_exec('DELETE FROM business_services WHERE id = ? AND business_id = ?', 'ii', [(int)($_POST['service_id'] ?? 0), $biz['id']]);
        flash('success', 'Service removed.'); redirect($back . 'services');
    }

    if ($action === 'add_images' && $biz) {
        $tab = 'photos';
        $count = (int)db_one('SELECT COUNT(*) n FROM business_images WHERE business_id = ?', 'i', [$biz['id']])['n'];
        $added = 0;
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $i => $n) {
                if ($count + $added >= $MAX_IMAGES) { $errors[] = "You can upload up to $MAX_IMAGES photos."; break; }
                $file = ['name' => $n, 'type' => $_FILES['images']['type'][$i], 'tmp_name' => $_FILES['images']['tmp_name'][$i],
                         'error' => $_FILES['images']['error'][$i], 'size' => $_FILES['images']['size'][$i]];
                $saved = upload_image($file, $upErr);
                if ($upErr) { $errors[] = $n . ': ' . $upErr; continue; }
                if ($saved) { db_exec('INSERT INTO business_images (business_id, image) VALUES (?, ?)', 'is', [$biz['id'], $saved]); $added++; }
            }
        }
        if ($added) flash('success', "$added photo(s) added. The first photo is used as your cover image.");
        if (!$errors) redirect($back . 'photos');
    }
    if ($action === 'delete_image' && $biz) {
        $img = db_one('SELECT * FROM business_images WHERE id = ? AND business_id = ?', 'ii', [(int)($_POST['image_id'] ?? 0), $biz['id']]);
        if ($img) { db_exec('DELETE FROM business_images WHERE id = ?', 'i', [$img['id']]); delete_upload($img['image']); flash('success', 'Photo removed.'); }
        redirect($back . 'photos');
    }
}

/* ---------- data for the overview ---------- */
$services = $images = $recent = [];
if ($biz) {
    $biz = db_one('SELECT * FROM businesses WHERE id = ?', 'i', [$biz['id']]);
    $bid = (int)$biz['id'];
    $services = db_all('SELECT * FROM business_services WHERE business_id = ? ORDER BY id', 'i', [$bid]);
    $images   = db_all('SELECT * FROM business_images WHERE business_id = ? ORDER BY id ASC', 'i', [$bid]);
    $recent   = db_all('SELECT r.*, u.name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.business_id = ? ORDER BY r.created_at DESC LIMIT 4', 'i', [$bid]);

    $today = date('Y-m-d'); $from = date('Y-m-d', strtotime('-29 days')); $pFrom = date('Y-m-d', strtotime('-59 days')); $pTo = date('Y-m-d', strtotime('-30 days'));
    $clicksF = ['calls', 'whatsapp', 'emails', 'websites'];
    $kViews  = [stat_sum($bid, ['views'], $from, $today), stat_sum($bid, ['views'], $pFrom, $pTo)];
    $kClicks = [stat_sum($bid, $clicksF, $from, $today), stat_sum($bid, $clicksF, $pFrom, $pTo)];
    $kCalls  = [stat_sum($bid, ['calls'], $from, $today), stat_sum($bid, ['calls'], $pFrom, $pTo)];
    $kRev    = [(int)db_one('SELECT COUNT(*) n FROM reviews WHERE business_id = ? AND DATE(created_at) BETWEEN ? AND ?', 'iss', [$bid, $from, $today])['n'],
                (int)db_one('SELECT COUNT(*) n FROM reviews WHERE business_id = ? AND DATE(created_at) BETWEEN ? AND ?', 'iss', [$bid, $pFrom, $pTo])['n']];
    $totalRev = (int)db_one('SELECT COUNT(*) n FROM reviews WHERE business_id = ?', 'i', [$bid])['n'];
    $avg = db_one('SELECT AVG(rating) a FROM reviews WHERE business_id = ?', 'i', [$bid])['a'];

    $rows = []; foreach (db_all('SELECT * FROM business_stats WHERE business_id = ? AND day BETWEEN ? AND ?', 'iss', [$bid, $from, $today]) as $r) $rows[$r['day']] = $r;
    $labels = $sv = $sc = [];
    for ($i = 29; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days")); $r = $rows[$d] ?? [];
        $labels[] = date('j M', strtotime($d));
        $sv[] = (int)($r['views'] ?? 0);
        $sc[] = (int)(($r['calls'] ?? 0) + ($r['whatsapp'] ?? 0) + ($r['emails'] ?? 0) + ($r['websites'] ?? 0));
    }
}
$val = fn($k) => e($_POST[$k] ?? ($biz[$k] ?? ''));
$selCat = (int)($_POST['category_id'] ?? ($biz['category_id'] ?? 0));
$selCity = $_POST['city'] ?? ($biz['city'] ?? '');
$selDays = $_POST['open_days'] ?? ($biz['open_days'] ?? 'daily');
$tv = fn($k) => e(substr((string)($_POST[$k] ?? ($biz[$k] ?? '')), 0, 5));

$pageTitle = "Business Dashboard | ConnectZW";
include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container dash">
    <div class="dash-head">
        <h1>Welcome back, <?php echo e($biz['business_name'] ?? $_SESSION['name']); ?> 👋</h1>
        <p>Here's what's happening with your business.</p>
    </div>

    <div class="dash-tabs">
        <?php foreach ($tabs as $k => $l): if (!$biz && $k !== 'listing') continue; ?>
            <a class="<?php echo $tab === $k ? 'on' : ''; ?>" href="<?php echo e(url($back . $k)); ?>"><?php echo e($l); ?></a>
        <?php endforeach; ?>
        <?php if ($biz): ?><a href="<?php echo e(url('business_profile.php?id=' . (int)$biz['id'])); ?>">View public page ↗</a><?php endif; ?>
    </div>

    <?php foreach ($errors as $er): ?><div class="alert alert-error"><?php echo e($er); ?></div><?php endforeach; ?>
    <?php if (!$biz): ?><div class="alert alert-info">You haven't listed your business yet. Fill in the form below to go live.</div><?php endif; ?>

<?php if ($tab === 'overview' && $biz): ?>
    <div class="kpis">
        <div class="kpi"><div class="k-top"><?php echo icon('eye', 16); ?> Profile Views</div><strong><?php echo number_format($kViews[0]); ?></strong><?php echo trend_html(pct_change($kViews[0], $kViews[1])); ?></div>
        <div class="kpi"><div class="k-top"><?php echo icon('cursor', 16); ?> Contact Clicks</div><strong><?php echo number_format($kClicks[0]); ?></strong><?php echo trend_html(pct_change($kClicks[0], $kClicks[1])); ?></div>
        <div class="kpi"><div class="k-top"><?php echo icon('phone', 16); ?> Calls</div><strong><?php echo number_format($kCalls[0]); ?></strong><?php echo trend_html(pct_change($kCalls[0], $kCalls[1])); ?></div>
        <div class="kpi"><div class="k-top"><?php echo icon('star', 16); ?> Reviews</div><strong><?php echo $totalRev; ?></strong><?php echo trend_html(pct_change($kRev[0], $kRev[1])); ?></div>
    </div>

    <div class="panel"><h2>Performance Overview <small class="muted" style="font-weight:500">· last 30 days</small></h2>
        <?php echo chart_line($labels, [['name' => 'Profile Views', 'color' => '#2563EB', 'data' => $sv], ['name' => 'Contact Clicks', 'color' => '#38BDF8', 'data' => $sc]]); ?></div>

    <div class="grid-2" style="margin-top:18px">
        <div class="panel"><div class="section-head" style="margin-bottom:6px"><h2>Recent Reviews</h2><span class="muted">Average <?php echo $avg !== null ? number_format((float)$avg, 1) : '–'; ?>/5</span></div>
            <?php if ($recent): foreach ($recent as $rv): ?>
                <div class="review"><div class="review-head"><div><span class="avatar"><?php echo e(strtoupper(substr($rv['name'], 0, 1))); ?></span><strong><?php echo e($rv['name']); ?></strong></div><span class="stars"><?php echo stars($rv['rating']); ?></span></div>
                    <p style="margin:6px 0 0"><?php echo e($rv['comment']); ?></p><small class="muted"><?php echo e(time_ago($rv['created_at'])); ?></small></div>
            <?php endforeach; else: ?><p class="muted">No reviews yet. Share your profile link to collect your first review.</p><?php endif; ?></div>
        <div class="panel"><h2>Quick checklist</h2>
            <ul class="checklist" style="margin-top:8px">
                <?php $todo = [['Add a description', !empty($biz['description'])], ['Add a logo', !empty($biz['logo'])], ['List your services', count($services) > 0], ['Upload photos', count($images) > 0], ['Set opening hours', !empty($biz['opens'])], ['Add email or website', !empty($biz['email']) || !empty($biz['website'])]];
                foreach ($todo as $t): ?>
                    <li><span class="ck" style="background:<?php echo $t[1] ? 'var(--green)' : '#CBD5E1'; ?>"><?php echo icon('check', 14); ?></span><?php echo e($t[0]); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php if (empty($biz['is_verified'])): ?><p class="muted" style="margin-top:14px;font-size:13px">Complete your profile and contact us to get the <strong>Verified</strong> badge.</p><?php else: ?><p style="margin-top:14px"><?php echo verified_badge(); ?></p><?php endif; ?>
        </div>
    </div>

<?php elseif ($tab === 'listing'): ?>
    <div class="panel" style="max-width:820px">
        <h2><?php echo $biz ? 'Edit business details' : 'Create your business listing'; ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?><input type="hidden" name="action" value="save">
            <div class="field"><label>Business name</label><input type="text" name="business_name" value="<?php echo $val('business_name'); ?>" maxlength="150" required></div>
            <div class="grid-2">
                <div class="field"><label>Category</label><select name="category_id" required><option value="">Choose a category</option>
                    <?php foreach ($cats as $c): ?><option value="<?php echo (int)$c['id']; ?>" <?php echo $selCat === (int)$c['id'] ? 'selected' : ''; ?>><?php echo e($c['category_name']); ?></option><?php endforeach; ?></select></div>
                <div class="field"><label>City or town</label><select name="city" required><option value="">Choose city or town</option>
                    <?php foreach (zw_cities() as $cn => $pv): ?><option value="<?php echo e($cn); ?>" <?php echo $selCity === $cn ? 'selected' : ''; ?>><?php echo e($cn); ?> (<?php echo e($pv); ?>)</option><?php endforeach; ?></select></div>
            </div>
            <div class="field"><label>Street address or area</label><input type="text" name="location" value="<?php echo $val('location'); ?>" placeholder="e.g. 123 Main Street, Avondale" maxlength="150" required></div>
            <div class="grid-2">
                <div class="field"><label>Phone / WhatsApp number</label><input type="text" name="phone" value="<?php echo $val('phone'); ?>" placeholder="e.g. 0771234567" maxlength="30" required></div>
                <div class="field"><label>Email <small class="muted">(optional)</small></label><input type="email" name="email" value="<?php echo $val('email'); ?>" maxlength="150"></div>
            </div>
            <div class="field"><label>Website <small class="muted">(optional)</small></label><input type="text" name="website" value="<?php echo $val('website'); ?>" placeholder="www.yourbusiness.co.zw" maxlength="200"></div>
            <div class="grid-2">
                <div class="field"><label>Opens</label><input type="time" name="opens" value="<?php echo $tv('opens'); ?>"></div>
                <div class="field"><label>Closes</label><input type="time" name="closes" value="<?php echo $tv('closes'); ?>"></div>
            </div>
            <div class="field"><label>Open on</label><select name="open_days">
                <?php foreach (['daily' => 'Every day', 'mon-sat' => 'Monday to Saturday', 'mon-fri' => 'Monday to Friday'] as $k => $l): ?><option value="<?php echo $k; ?>" <?php echo $selDays === $k ? 'selected' : ''; ?>><?php echo $l; ?></option><?php endforeach; ?></select></div>
            <div class="field"><label>Description</label><textarea name="description" maxlength="3000" placeholder="Tell customers what you offer, your experience, your prices..."><?php echo $val('description'); ?></textarea></div>
            <div class="field"><label>Logo <small class="muted">(JPG, PNG or WebP, max 3MB)</small></label><input type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                <?php if ($biz && $biz['logo']): ?><small class="muted">A logo is already uploaded. Choose a file to replace it.</small><?php endif; ?></div>
            <button type="submit"><?php echo $biz ? 'Save changes' : 'Publish my business'; ?></button>
        </form>
    </div>

<?php elseif ($tab === 'services' && $biz): ?>
    <div class="panel" style="max-width:760px">
        <h2>Services you offer</h2>
        <p class="muted">List what your business does so customers know exactly what to ask for (e.g. "Full car service", "Geyser installation"). Customers can also find you by searching these.</p>
        <form method="POST" style="display:grid;grid-template-columns:2fr 1fr auto;gap:10px;margin-bottom:14px">
            <?php echo csrf_field(); ?><input type="hidden" name="action" value="add_service">
            <input type="text" name="service_name" placeholder="Service name" maxlength="120" required>
            <input type="text" name="price" placeholder="Price (optional)" maxlength="60">
            <button type="submit">Add</button>
        </form>
        <?php foreach ($services as $sv): ?>
            <div class="svc-row"><span><?php echo icon('check', 15); ?> <?php echo e($sv['service_name']); ?> <?php if ($sv['price']): ?><strong class="muted">· <?php echo e($sv['price']); ?></strong><?php endif; ?></span>
                <form method="POST" class="inline-form"><?php echo csrf_field(); ?><input type="hidden" name="action" value="delete_service"><input type="hidden" name="service_id" value="<?php echo (int)$sv['id']; ?>"><button type="submit" class="btn btn-danger btn-sm">Remove</button></form></div>
        <?php endforeach; ?>
        <?php if (!$services): ?><p class="muted">No services listed yet.</p><?php endif; ?>
    </div>

<?php elseif ($tab === 'photos' && $biz): ?>
    <div class="panel" style="max-width:820px">
        <h2>Photo gallery</h2>
        <p class="muted">Show customers your work. Up to <?php echo $MAX_IMAGES; ?> photos (<?php echo count($images); ?> uploaded). Your first photo becomes your cover image.</p>
        <form method="POST" enctype="multipart/form-data" style="margin-bottom:18px">
            <?php echo csrf_field(); ?><input type="hidden" name="action" value="add_images">
            <div class="field"><input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple></div>
            <button type="submit">Upload photos</button>
        </form>
        <?php if ($images): ?><div class="thumb-row"><?php foreach ($images as $img): ?>
            <div class="thumb-item"><img src="<?php echo e(upload_url($img['image'])); ?>" alt="">
                <form method="POST" onsubmit="return confirm('Remove this photo?');"><?php echo csrf_field(); ?><input type="hidden" name="action" value="delete_image"><input type="hidden" name="image_id" value="<?php echo (int)$img['id']; ?>"><button type="submit" class="btn btn-danger btn-sm btn-block">Remove</button></form></div>
        <?php endforeach; ?></div><?php endif; ?>
    </div>
<?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
