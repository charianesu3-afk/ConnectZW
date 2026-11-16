<?php
// Counts a call / WhatsApp / email / website click (max once per 5 minutes per visitor)
require_once __DIR__ . '/includes/config.php';

$bid = (int)($_GET['b'] ?? 0);
$map = ['call' => 'calls', 'whatsapp' => 'whatsapp', 'email' => 'emails', 'website' => 'websites'];
$t = $map[$_GET['t'] ?? ''] ?? null;

if ($bid > 0 && $t && db_one('SELECT id FROM businesses WHERE id = ?', 'i', [$bid])) {
    $last = $_SESSION['clicked'][$bid][$t] ?? 0;
    if (time() - $last > 300) {
        track_stat($bid, $t);
        $_SESSION['clicked'][$bid][$t] = time();
    }
}
http_response_code(204);
