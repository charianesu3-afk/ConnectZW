<?php
require_once __DIR__ . '/includes/config.php';
require_login();
require_post();

$uid = (int)$_SESSION['user_id'];
$rid = (int)($_POST['review_id'] ?? 0);

$review = db_one('SELECT id, user_id, business_id FROM reviews WHERE id = ?', 'i', [$rid]);
if (!$review || ((int)$review['user_id'] !== $uid && current_role() !== 'admin')) {
    flash('error', 'Review not found or you do not have permission.');
    redirect('index.php');
}

db_exec('DELETE FROM reviews WHERE id = ?', 'i', [$rid]);
flash('success', 'Review deleted.');

$next = ($_POST['next'] ?? '') === 'my_reviews' ? 'customer/my_reviews.php' : 'business_profile.php?id=' . (int)$review['business_id'];
redirect($next);
