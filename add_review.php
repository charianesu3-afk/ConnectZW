<?php
require_once __DIR__ . '/includes/config.php';
require_login();
require_post();

$uid     = (int)$_SESSION['user_id'];
$bid     = (int)($_POST['business_id'] ?? 0);
$rating  = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$back    = 'business_profile.php?id=' . $bid;

$biz = db_one('SELECT id, user_id FROM businesses WHERE id = ?', 'i', [$bid]);

if (!$biz)                                  { flash('error', 'Business not found.'); redirect('search.php'); }
if ((int)$biz['user_id'] === $uid)          { flash('error', "You can't review your own business."); redirect($back); }
if ($rating < 1 || $rating > 5)             { flash('error', 'Please choose a rating from 1 to 5.'); redirect($back); }
if (strlen($comment) < 5)                   { flash('error', 'Please write a short review (at least 5 characters).'); redirect($back); }
if (strlen($comment) > 1000)                { flash('error', 'Your review is too long (max 1000 characters).'); redirect($back); }
if (db_one('SELECT id FROM reviews WHERE user_id = ? AND business_id = ?', 'ii', [$uid, $bid])) {
    flash('error', 'You have already reviewed this business.'); redirect($back);
}

db_exec('INSERT INTO reviews (business_id, user_id, rating, comment) VALUES (?, ?, ?, ?)', 'iiis', [$bid, $uid, $rating, $comment]);
flash('success', 'Thank you! Your review has been posted.');
redirect($back);
