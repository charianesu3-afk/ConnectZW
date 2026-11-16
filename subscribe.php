<?php
require_once __DIR__ . '/includes/config.php';
require_post();

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
    flash('error', 'Please enter a valid email address.');
} else {
    db_exec('INSERT IGNORE INTO subscribers (email) VALUES (?)', 's', [$email]);
    flash('success', 'Thanks for subscribing to the ConnectZW newsletter!');
}
$back = $_SERVER['HTTP_REFERER'] ?? '';
$host = parse_url($back, PHP_URL_HOST);
if ($back && $host === ($_SERVER['HTTP_HOST'] ?? '') && strpos($back, 'subscribe.php') === false) { header('Location: ' . $back); exit(); }
redirect('index.php');
