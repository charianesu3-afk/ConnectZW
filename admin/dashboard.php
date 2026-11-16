<?php
require_once __DIR__ . '/../includes/config.php';
require_role('admin');

$me = (int)$_SESSION['user_id'];
$pages = [
    'overview'   => ['Dashboard', 'layout'], 'businesses' => ['Businesses', 'store'], 'categories' => ['Categories', 'layers'],
    'users'      => ['Users', 'users'],      'reviews'    => ['Reviews', 'star'],     'blog'       => ['Blog', 'file'],
    'reports'    => ['Reports', 'chart'],    'settings'   => ['Settings', 'sliders'],
];
$p = $_GET['p'] ?? 'overview';
if (!isset($pages[$p])) $p = 'overview';
$self = 'admin/dashboard.php?p=';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $go = $self . $p;

    switch ($action) {
        case 'set_role':
            $role = $_POST['role'] ?? '';
            if ($id === $me) flash('error', "You can't change your own role.");
            elseif (in_array($role, ['customer', 'business', 'admin'], true)) { db_exec('UPDATE users SET role = ? WHERE id = ?', 'si', [$role, $id]); flash('success', 'User role updated.'); }
            break;
        case 'delete_user':
            if ($id === $me) { flash('error', "You can't delete your own account."); break; }
            foreach (db_all('SELECT id, logo FROM businesses WHERE user_id = ?', 'i', [$id]) as $bz) {
                delete_upload($bz['logo']);
                foreach (db_all('SELECT image FROM business_images WHERE business_id = ?', 'i', [$bz['id']]) as $im) delete_upload($im['image']);
            }
            db_exec('DELETE FROM users WHERE id = ?', 'i', [$id]); flash('success', 'User deleted.');
            break;
        case 'toggle_verify':
            db_exec('UPDATE businesses SET is_verified = 1 - is_verified WHERE id = ?', 'i', [$id]); flash('success', 'Verification updated.');
            break;
        case 'delete_business':
            $bz = db_one('SELECT logo FROM businesses WHERE id = ?', 'i', [$id]);
            if ($bz) {
                delete_upload($bz['logo']);
                foreach (db_all('SELECT image FROM business_images WHERE business_id = ?', 'i', [$id]) as $im) delete_upload($im['image']);
                db_exec('DELETE FROM businesses WHERE id = ?', 'i', [$id]); flash('success', 'Business deleted.');
            }
            break;
        case 'add_category':
            $nm = trim($_POST['category_name'] ?? '');
            if ($nm === '' || strlen($nm) > 150) flash('error', 'Enter a category name (max 150 characters).');
            elseif (db_one('SELECT id FROM categories WHERE category_name = ?', 's', [$nm])) flash('error', 'That category already exists.');
            else { db_exec('INSERT INTO categories (category_name) VALUES (?)', 's', [$nm]); flash('success', 'Category added.'); }
            break;
        case 'delete_category': db_exec('DELETE FROM categories WHERE id = ?', 'i', [$id]); flash('success', 'Category deleted.'); break;
        case 'delete_review':   db_exec('DELETE FROM reviews WHERE id = ?', 'i', [$id]); flash('success', 'Review deleted.'); break;
        case 'delete_message':  db_exec('DELETE FROM contact_messages WHERE id = ?', 'i', [$id]); flash('success', 'Message deleted.'); break;
        case 'delete_sub':      db_exec('DELETE FROM subscribers WHERE id = ?', 'i', [$id]); flash('success', 'Subscriber removed.'); break;
        case 'delete_post':
            $po = db_one('SELECT image FROM posts WHERE id = ?', 'i', [$id]);
            if ($po) { delete_upload($po['image']); db_exec('DELETE FROM posts WHERE id = ?', 'i', [$id]); flash('success', 'Post deleted.'); }
            break;
        case 'save_post':
            $title = trim($_POST['title'] ?? ''); $exc = trim($_POST['excerpt'] ?? ''); $body = trim($_POST['content'] ?? '');
            if ($title === '' || strlen($title) > 200 || $body === '') { flash('error', 'A title and some content are required.'); $go = $self . 'blog' . ($id ? '&edit=' . $id : '&new=1'); break; }
            $img = upload_image($_FILES['image'] ?? [], $upErr);
            if ($upErr) { flash('error', $upErr); $go = $self . 'blog' . ($id ? '&edit=' . $id : '&new=1'); break; }
            if ($id) {
                $old = db_one('SELECT image FROM posts WHERE id = ?', 'i', [$id]);
                if ($img && $old) delete_upload($old['image']);
                db_exec('UPDATE posts SET title = ?, excerpt = ?, content = ?, image = ? WHERE id = ?', 'ssssi', [$title, substr($exc, 0, 300), $body, $img ?? ($old['image'] ?? null), $id]);
            } else {
                $slug = slugify($title);
                if (db_one('SELECT id FROM posts WHERE slug = ?', 's', [$slug])) $slug .= '-' . time();
                db_exec('INSERT INTO posts (title, slug, excerpt, content, image) VALUES (?,?,?,?,?)', 'sssss', [$title, $slug, substr($exc, 0, 300), $body, $img]);
            }
            flash('success', 'Post saved.'); $go = $self . 'blog';
            break;
        case 'change_password':
            $cur = $_POST['current'] ?? ''; $new = $_POST['new'] ?? ''; $conf = $_POST['confirm'] ?? '';
            $row = db_one('SELECT password FROM users WHERE id = ?', 'i', [$me]);
            if (!$row || !password_verify($cur, $row['password'])) flash('error', 'Your current password is incorrect.');
            elseif (strlen($new) < 8) flash('error', 'The new password must be at least 8 characters.');
            elseif ($new !== $conf) flash('error', 'The new passwords do not match.');
            else { db_exec('UPDATE users SET password = ? WHERE id = ?', 'si', [password_hash($new, PASSWORD_DEFAULT), $me]); flash('success', 'Password changed.'); }
            break;
    }
    redirect($go);
}

function del_form($action, $id, $label = 'Delete', $confirm = 'Are you sure?') {
    return '<form method="POST" class="inline-form" onsubmit="return confirm(\'' . e($confirm) . '\');">' . csrf_field() .
        '<input type="hidden" name="action" value="' . e($action) . '"><input type="hidden" name="id" value="' . (int)$id . '">' .
        '<button type="submit" class="btn btn-danger btn-sm">' . e($label) . '</button></form>';
}

$pageTitle = "Admin | ConnectZW";
include '../includes/header.php';
?>
<div class="admin-body">
    <aside class="sidebar">
        <a class="brand" href="<?php echo e(url('index.php')); ?>"><?php echo icon('pin', 24); ?> ConnectZW</a>
        <nav>
            <?php foreach ($pages as $k => $info): ?>
                <a class="<?php echo $p === $k ? 'on' : ''; ?>" href="<?php echo e(url($self . $k)); ?>"><?php echo icon($info[1], 18); ?> <?php echo e($info[0]); ?></a>
            <?php endforeach; ?>
            <div class="sep"></div>
            <a href="<?php echo e(url('index.php')); ?>"><?php echo icon('globe', 18); ?> View website</a>
            <a href="<?php echo e(url('logout.php')); ?>"><?php echo icon('logout', 18); ?> Logout</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-top"><h1><?php echo $p === 'overview' ? 'Dashboard Overview' : e($pages[$p][0]); ?></h1><span class="muted">Signed in as <?php echo e($_SESSION['name']); ?></span></div>
        <?php flash_render(); ?>

<?php if ($p === 'overview'):
    $d30 = date('Y-m-d', strtotime('-30 days')); $d60 = date('Y-m-d', strtotime('-60 days'));
    $grow = function ($table) use ($d30, $d60) {
        $a = (int)db_one("SELECT COUNT(*) n FROM $table WHERE created_at >= ?", 's', [$d30])['n'];
        $b = (int)db_one("SELECT COUNT(*) n FROM $table WHERE created_at >= ? AND created_at < ?", 'ss', [$d60, $d30])['n'];
        return pct_change($a, $b);
    };
    $totals = [
        ['Total Businesses', 'store', (int)db_one('SELECT COUNT(*) n FROM businesses')['n'], $grow('businesses')],
        ['Total Users', 'users', (int)db_one('SELECT COUNT(*) n FROM users')['n'], $grow('users')],
        ['Total Reviews', 'star', (int)db_one('SELECT COUNT(*) n FROM reviews')['n'], $grow('reviews')],
        ['Total Categories', 'layers', (int)db_one('SELECT COUNT(*) n FROM categories')['n'], null],
    ];
    $gl = []; $gd = [];
    for ($i = 5; $i >= 0; $i--) {
        $ts = strtotime("first day of -$i month");
        $gl[] = date('M', $ts);
        $gd[] = (int)db_one('SELECT COUNT(*) n FROM businesses WHERE created_at <= ?', 's', [date('Y-m-t 23:59:59', $ts)])['n'];
    }
    $catRows = db_all('SELECT c.category_name, COUNT(b.id) n FROM categories c JOIN businesses b ON b.category_id = c.id GROUP BY c.id, c.category_name ORDER BY n DESC');
    $colors = ['#2563EB', '#38BDF8', '#7C3AED', '#F59E0B', '#10B981', '#94A3B8'];
    $donut = []; $other = 0;
    foreach ($catRows as $i => $r) { if ($i < 5) $donut[] = ['label' => $r['category_name'], 'value' => (int)$r['n'], 'color' => $colors[$i]]; else $other += (int)$r['n']; }
    if ($other) $donut[] = ['label' => 'Others', 'value' => $other, 'color' => $colors[5]];
    $recentB = db_all(BUSINESS_SELECT . ' ORDER BY b.created_at DESC LIMIT 5');
    $posts3 = db_all('SELECT * FROM posts ORDER BY created_at DESC LIMIT 3');
?>
    <div class="kpis">
        <?php foreach ($totals as $t): ?>
            <div class="kpi"><div class="k-top"><?php echo icon($t[1], 16); ?> <?php echo e($t[0]); ?></div><strong><?php echo number_format($t[2]); ?></strong><?php echo $t[3] !== null ? trend_html($t[3], 'vs last month') : '<small class="muted">All categories</small>'; ?></div>
        <?php endforeach; ?>
    </div>
    <div class="grid-2">
        <div class="panel"><h3>Businesses Growth</h3><?php echo chart_line($gl, [['name' => 'Businesses', 'color' => '#2563EB', 'data' => $gd]], 230); ?></div>
        <div class="panel"><h3>Businesses by Category</h3><?php echo $donut ? chart_donut($donut) : '<p class="muted">No businesses yet.</p>'; ?></div>
    </div>
    <div class="grid-2" style="margin-top:18px">
        <div class="panel"><div class="section-head" style="margin-bottom:12px"><h3 style="margin:0">Recent Businesses</h3><a href="<?php echo e(url($self . 'businesses')); ?>">View all</a></div>
            <div class="mini-list"><?php foreach ($recentB as $b): ?>
                <div class="mini"><div class="mi"><?php echo category_icon($b['category_name'] ?? '', 18); ?></div><div><strong><?php echo e($b['business_name']); ?></strong><small><?php echo e(biz_city($b)); ?> · <?php echo e(time_ago($b['created_at'])); ?></small></div></div>
            <?php endforeach; if (!$recentB) echo '<p class="muted">No businesses yet.</p>'; ?></div></div>
        <div class="panel"><div class="section-head" style="margin-bottom:12px"><h3 style="margin:0">Latest From Our Blog</h3><a href="<?php echo e(url($self . 'blog')); ?>">Manage</a></div>
            <div class="mini-list"><?php foreach ($posts3 as $po): ?>
                <div class="mini"><div class="mi"><?php echo icon('file', 18); ?></div><div><strong><?php echo e($po['title']); ?></strong><small><?php echo e(date('M j, Y', strtotime($po['created_at']))); ?></small></div></div>
            <?php endforeach; if (!$posts3) echo '<p class="muted">No posts yet.</p>'; ?></div></div>
    </div>

<?php elseif ($p === 'businesses'): $rows = db_all('SELECT b.id, b.business_name, b.city, b.views, b.is_verified, c.category_name, u.name AS owner
        FROM businesses b LEFT JOIN categories c ON b.category_id = c.id LEFT JOIN users u ON b.user_id = u.id ORDER BY b.id DESC LIMIT 400'); ?>
    <div class="table-wrap"><table class="data"><tr><th>Business</th><th>Category</th><th>City</th><th>Owner</th><th>Views</th><th>Verified</th><th>Actions</th></tr>
        <?php foreach ($rows as $b): ?><tr>
            <td><a href="<?php echo e(url('business_profile.php?id=' . (int)$b['id'])); ?>"><?php echo e($b['business_name']); ?></a></td>
            <td><?php echo e($b['category_name'] ?? '—'); ?></td><td><?php echo e($b['city']); ?></td><td><?php echo e($b['owner'] ?? '—'); ?></td><td><?php echo (int)$b['views']; ?></td>
            <td><form method="POST" class="inline-form"><?php echo csrf_field(); ?><input type="hidden" name="action" value="toggle_verify"><input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                <button type="submit" class="btn btn-sm <?php echo $b['is_verified'] ? '' : 'btn-light'; ?>"><?php echo $b['is_verified'] ? '✔ Verified' : 'Verify'; ?></button></form></td>
            <td><?php echo del_form('delete_business', $b['id'], 'Delete', 'Delete this business and its reviews?'); ?></td></tr>
        <?php endforeach; if (!$rows) echo '<tr><td colspan="7" class="muted">No businesses yet.</td></tr>'; ?></table></div>

<?php elseif ($p === 'categories'): $rows = db_all('SELECT c.id, c.category_name, COUNT(b.id) AS total FROM categories c LEFT JOIN businesses b ON b.category_id = c.id GROUP BY c.id, c.category_name ORDER BY c.category_name'); ?>
    <div class="panel" style="margin-bottom:18px"><form method="POST" style="display:flex;gap:10px;flex-wrap:wrap"><?php echo csrf_field(); ?><input type="hidden" name="action" value="add_category">
        <input type="text" name="category_name" placeholder="New category name" maxlength="150" style="flex:1;min-width:220px" required><button type="submit">Add category</button></form>
        <p class="muted" style="margin:10px 0 0;font-size:13px">Add a category photo by saving it as <code>assets/images/categories/&lt;category-name&gt;.jpg</code> (see the README in that folder).</p></div>
    <div class="table-wrap"><table class="data"><tr><th>Photo</th><th>Category</th><th>Businesses</th><th>Photo file name</th><th>Actions</th></tr>
        <?php foreach ($rows as $c): $img = category_image($c['category_name']); ?><tr>
            <td><?php echo $img ? '<img src="' . e($img) . '" alt="" style="width:64px;height:40px;object-fit:cover;border-radius:6px">' : '<span style="color:var(--blue)">' . category_icon($c['category_name'], 22) . '</span>'; ?></td>
            <td><?php echo e($c['category_name']); ?></td><td><?php echo (int)$c['total']; ?></td><td><code><?php echo e(category_slug($c['category_name'])); ?>.jpg</code> <?php echo $img ? '✅' : '❌'; ?></td>
            <td><?php echo del_form('delete_category', $c['id'], 'Delete', 'Delete this category?'); ?></td></tr><?php endforeach; ?></table></div>

<?php elseif ($p === 'users'): $rows = db_all('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC LIMIT 400'); ?>
    <div class="table-wrap"><table class="data"><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr>
        <?php foreach ($rows as $u): ?><tr><td><?php echo e($u['name']); ?></td><td><?php echo e($u['email']); ?></td>
            <td><form method="POST" class="inline-form" style="display:flex;gap:6px"><?php echo csrf_field(); ?><input type="hidden" name="action" value="set_role"><input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                <select name="role" style="padding:6px 8px;width:auto" <?php echo (int)$u['id'] === $me ? 'disabled' : ''; ?>><?php foreach (['customer', 'business', 'admin'] as $r): ?><option value="<?php echo $r; ?>" <?php echo $u['role'] === $r ? 'selected' : ''; ?>><?php echo $r; ?></option><?php endforeach; ?></select>
                <?php if ((int)$u['id'] !== $me): ?><button type="submit" class="btn btn-light btn-sm">Save</button><?php endif; ?></form></td>
            <td><?php echo e(date('d M Y', strtotime($u['created_at']))); ?></td>
            <td><?php echo (int)$u['id'] === $me ? '<span class="muted">You</span>' : del_form('delete_user', $u['id'], 'Delete', 'Delete this user and all their data?'); ?></td></tr><?php endforeach; ?></table></div>

<?php elseif ($p === 'reviews'): $rows = db_all('SELECT r.id, r.rating, r.comment, r.created_at, u.name, b.business_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN businesses b ON r.business_id = b.id ORDER BY r.id DESC LIMIT 300'); ?>
    <div class="table-wrap"><table class="data"><tr><th>Business</th><th>Reviewer</th><th>Rating</th><th>Comment</th><th>Date</th><th>Actions</th></tr>
        <?php foreach ($rows as $r): ?><tr><td><?php echo e($r['business_name']); ?></td><td><?php echo e($r['name']); ?></td><td class="stars"><?php echo stars($r['rating']); ?></td>
            <td style="max-width:320px"><?php echo e($r['comment']); ?></td><td><?php echo e(date('d M Y', strtotime($r['created_at']))); ?></td><td><?php echo del_form('delete_review', $r['id'], 'Delete', 'Delete this review?'); ?></td></tr>
        <?php endforeach; if (!$rows) echo '<tr><td colspan="6" class="muted">No reviews yet.</td></tr>'; ?></table></div>

<?php elseif ($p === 'blog'):
    $editId = (int)($_GET['edit'] ?? 0); $showForm = $editId || isset($_GET['new']);
    $edit = $editId ? db_one('SELECT * FROM posts WHERE id = ?', 'i', [$editId]) : null;
    if ($showForm): ?>
    <div class="panel" style="max-width:820px"><h2><?php echo $edit ? 'Edit post' : 'New post'; ?></h2>
        <form method="POST" enctype="multipart/form-data"><?php echo csrf_field(); ?><input type="hidden" name="action" value="save_post"><input type="hidden" name="id" value="<?php echo (int)($edit['id'] ?? 0); ?>">
            <div class="field"><label>Title</label><input type="text" name="title" maxlength="200" value="<?php echo e($edit['title'] ?? ''); ?>" required></div>
            <div class="field"><label>Short summary</label><input type="text" name="excerpt" maxlength="300" value="<?php echo e($edit['excerpt'] ?? ''); ?>"></div>
            <div class="field"><label>Content <small class="muted">(blank line = new paragraph; start a line with ## for a heading)</small></label><textarea name="content" style="min-height:260px" required><?php echo e($edit['content'] ?? ''); ?></textarea></div>
            <div class="field"><label>Cover image <small class="muted">(optional)</small></label><input type="file" name="image" accept="image/jpeg,image/png,image/webp"></div>
            <button type="submit">Save post</button> <a class="btn btn-light" href="<?php echo e(url($self . 'blog')); ?>">Cancel</a></form></div>
    <?php else: $rows = db_all('SELECT * FROM posts ORDER BY created_at DESC'); ?>
    <p><a class="btn" href="<?php echo e(url($self . 'blog&new=1')); ?>">+ New post</a></p>
    <div class="table-wrap"><table class="data"><tr><th>Title</th><th>Published</th><th>Actions</th></tr>
        <?php foreach ($rows as $po): ?><tr><td><a href="<?php echo e(url('blog_post.php?slug=' . urlencode($po['slug']))); ?>"><?php echo e($po['title']); ?></a></td><td><?php echo e(date('d M Y', strtotime($po['created_at']))); ?></td>
            <td><a class="btn btn-light btn-sm" href="<?php echo e(url($self . 'blog&edit=' . (int)$po['id'])); ?>">Edit</a> <?php echo del_form('delete_post', $po['id'], 'Delete', 'Delete this post?'); ?></td></tr>
        <?php endforeach; if (!$rows) echo '<tr><td colspan="3" class="muted">No posts yet.</td></tr>'; ?></table></div>
    <?php endif; ?>

<?php elseif ($p === 'reports'):
    $top = db_all('SELECT business_name, city, views FROM businesses ORDER BY views DESC LIMIT 8');
    $msgs = db_all('SELECT * FROM contact_messages ORDER BY id DESC LIMIT 50');
    $subs = db_all('SELECT * FROM subscribers ORDER BY id DESC LIMIT 30');
    $subTotal = (int)db_one('SELECT COUNT(*) n FROM subscribers')['n']; ?>
    <div class="grid-2">
        <div class="panel"><h3>Most viewed businesses</h3><div class="mini-list"><?php foreach ($top as $t): ?>
            <div class="mini"><div class="mi"><?php echo icon('eye', 18); ?></div><div><strong><?php echo e($t['business_name']); ?></strong><small><?php echo e($t['city']); ?> · <?php echo number_format((int)$t['views']); ?> views</small></div></div><?php endforeach; ?></div></div>
        <div class="panel"><h3>Newsletter subscribers (<?php echo $subTotal; ?>)</h3><?php foreach ($subs as $s): ?>
            <div class="svc-row"><span><?php echo e($s['email']); ?></span><?php echo del_form('delete_sub', $s['id'], 'Remove', 'Remove this subscriber?'); ?></div><?php endforeach; if (!$subs) echo '<p class="muted">No subscribers yet.</p>'; ?></div>
    </div>
    <h3 style="margin:26px 0 12px">Contact messages</h3>
    <?php foreach ($msgs as $m): ?><div class="panel"><div class="review-head"><h3 style="margin:0"><?php echo e($m['subject']); ?></h3><small class="muted"><?php echo e(date('d M Y H:i', strtotime($m['created_at']))); ?></small></div>
        <p class="muted" style="margin:4px 0 10px">From <?php echo e($m['name']); ?> · <a href="mailto:<?php echo e($m['email']); ?>"><?php echo e($m['email']); ?></a></p>
        <p><?php echo nl2br(e($m['message'])); ?></p><?php echo del_form('delete_message', $m['id'], 'Delete', 'Delete this message?'); ?></div>
    <?php endforeach; if (!$msgs) echo '<div class="empty-box"><h3>No messages yet</h3></div>'; ?>

<?php elseif ($p === 'settings'): ?>
    <div class="panel" style="max-width:460px"><h2>Change your password</h2>
        <form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="action" value="change_password">
            <div class="field"><label>Current password</label><input type="password" name="current" required autocomplete="current-password"></div>
            <div class="field"><label>New password <small class="muted">(8+ characters)</small></label><input type="password" name="new" minlength="8" required autocomplete="new-password"></div>
            <div class="field"><label>Confirm new password</label><input type="password" name="confirm" minlength="8" required autocomplete="new-password"></div>
            <button type="submit">Update password</button></form></div>
<?php endif; ?>
    </main>
</div>
<script src="<?php echo e(url('assets/js/main.js')); ?>?v=3"></script>
</body>
</html>
