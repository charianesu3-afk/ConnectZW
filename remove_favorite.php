<?php
require_once __DIR__ . '/includes/config.php';
require_login();
require_post();

$uid = (int)$_SESSION['user_id'];
$bid = (int)($_POST['business_id'] ?? 0);
db_exec('DELETE FROM favorites WHERE user_id = ? AND business_id = ?', 'ii', [$uid, $bid]);
flash('success', 'Removed from your favorites.');

$next = ($_POST['next'] ?? '') === 'favorites' ? 'customer/my_favorites.php' : 'business_profile.php?id=' . $bid;
redirect($next);
