<?php
require_once __DIR__ . '/includes/config.php';
require_login();
require_post();

$uid = (int)$_SESSION['user_id'];
$bid = (int)($_POST['business_id'] ?? 0);

if ($bid > 0 && db_one('SELECT id FROM businesses WHERE id = ?', 'i', [$bid])) {
    db_exec('INSERT IGNORE INTO favorites (user_id, business_id) VALUES (?, ?)', 'ii', [$uid, $bid]);
    flash('success', 'Saved to your favorites.');
}
redirect('business_profile.php?id=' . $bid);
