<?php
/* Simple SVG charts (no external libraries) */
function chart_line($labels, $series, $h = 240) {
    $w = 700; $padL = 42; $padR = 12; $padT = 28; $padB = 30;
    $max = 0;
    foreach ($series as $s) foreach ($s['data'] as $v) $max = max($max, $v);
    $max = max(4, $max);
    $step = pow(10, floor(log10($max))); 
    $top = ceil($max / $step) * $step; if ($top / $step < 2) $top = $step * 2;
    $n = max(1, count($labels) - 1);
    $x = fn($i) => $padL + ($w - $padL - $padR) * ($i / $n);
    $y = fn($v) => $padT + ($h - $padT - $padB) * (1 - $v / $top);
    $o = '<svg class="chart" viewBox="0 0 ' . $w . ' ' . $h . '" width="100%" role="img" aria-label="Line chart">';
    for ($g = 0; $g <= 4; $g++) {
        $val = $top * $g / 4; $yy = $y($val);
        $o .= '<line x1="' . $padL . '" y1="' . $yy . '" x2="' . ($w - $padR) . '" y2="' . $yy . '" stroke="#E5EAF3"/>';
        $o .= '<text x="' . ($padL - 8) . '" y="' . ($yy + 4) . '" text-anchor="end" font-size="11" fill="#94A3B8">' . number_format($val) . '</text>';
    }
    $every = max(1, (int)ceil(count($labels) / 6));
    foreach ($labels as $i => $lb) {
        if ($i % $every === 0) $o .= '<text x="' . $x($i) . '" y="' . ($h - 8) . '" text-anchor="middle" font-size="11" fill="#94A3B8">' . e($lb) . '</text>';
    }
    $lx = $w - $padR;
    foreach (array_reverse($series, true) as $k => $s) {
        $pts = [];
        foreach ($s['data'] as $i => $v) $pts[] = round($x($i), 1) . ',' . round($y($v), 1);
        $o .= '<polyline fill="none" stroke="' . e($s['color']) . '" stroke-width="2.4" stroke-linejoin="round" stroke-linecap="round" points="' . implode(' ', $pts) . '"/>';
    }
    foreach (array_values($series) as $k => $s) {
        $lx = $w - $padR - ($k * 110);
        $o .= '<circle cx="' . ($lx - 98) . '" cy="12" r="4" fill="' . e($s['color']) . '"/><text x="' . ($lx - 90) . '" y="16" font-size="11" fill="#64748B">' . e($s['name']) . '</text>';
    }
    return $o . '</svg>';
}

function chart_donut($items) {
    $total = 0; foreach ($items as $it) $total += $it['value'];
    $total = max(1, $total);
    $r = 62; $c = 2 * M_PI * $r; $off = 0;
    $o = '<div class="donut-wrap"><svg class="donut" viewBox="0 0 160 160" width="170" height="170" role="img" aria-label="Donut chart"><g transform="rotate(-90 80 80)">';
    foreach ($items as $it) {
        $len = $c * $it['value'] / $total;
        $o .= '<circle cx="80" cy="80" r="' . $r . '" fill="none" stroke="' . e($it['color']) . '" stroke-width="24" stroke-dasharray="' . round($len, 2) . ' ' . round($c - $len, 2) . '" stroke-dashoffset="' . round(-$off, 2) . '"/>';
        $off += $len;
    }
    $o .= '</g></svg><ul class="legend">';
    foreach ($items as $it) {
        $o .= '<li><i style="background:' . e($it['color']) . '"></i>' . e($it['label']) . '<b>' . round($it['value'] * 100 / $total) . '%</b></li>';
    }
    return $o . '</ul></div>';
}
