<?php
/* Clean outline icons (inline SVG) */
function icon($name, $size = 20, $class = '') {
    static $p = [
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
        'pin'      => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'phone'    => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
        'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
        'chat'     => '<path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7A8.4 8.4 0 0 1 4 11.5 8.5 8.5 0 0 1 8.7 3.9 8.4 8.4 0 0 1 12.5 3h.5a8.5 8.5 0 0 1 8 8z"/>',
        'star'     => '<path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.9L12 17.8 5.8 21.1 7 14.2 2 9.3l6.9-1z"/>',
        'heart'    => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8z"/>',
        'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>',
        'check'    => '<path d="M5 12l5 5L20 7"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>',
        'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6.5 6.5 0 0 1 3.5 6"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'home'     => '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
        'bag'      => '<path d="M5 8h14l-1 12H6L5 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
        'grid'     => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'dots'     => '<circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'tool'     => '<path d="M14.7 6.3a4 4 0 0 0 5 5L22 9.6V8l-3 1-2-2 1-3h-1.6l-1.7 2.3zM14 10L4.5 19.5a2 2 0 0 0 3 3L17 13"/>',
        'car'      => '<path d="M4 16l1.6-5.2A2.5 2.5 0 0 1 8 9h8a2.5 2.5 0 0 1 2.4 1.8L20 16"/><rect x="3" y="16" width="18" height="4" rx="1.5"/><circle cx="7.5" cy="18" r=".6"/><circle cx="16.5" cy="18" r=".6"/><path d="M5 20v1.5M19 20v1.5"/>',
        'utensils' => '<path d="M4 3v7a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V3M7 3v18M19 21V3a5 5 0 0 0-5 5v5a2 2 0 0 0 2 2h3"/>',
        'hardhat'  => '<path d="M3 18h18v2H3z"/><path d="M5 18v-3a7 7 0 0 1 14 0v3"/><path d="M10 8V5h4v3M12 8v5"/>',
        'smile'    => '<circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2.5 4 2.5 4-2.5 4-2.5"/><path d="M9 9.5h.01M15 9.5h.01"/>',
        'plus-circle' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'briefcase'=> '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
        'book'     => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 21.5V4.5"/>',
        'monitor'  => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 21h8M12 16v5"/>',
        'truck'    => '<path d="M2 6h11v10H2zM13 9h4l4 4v3h-8z"/><circle cx="6.5" cy="17.5" r="2"/><circle cx="17.5" cy="17.5" r="2"/>',
        'leaf'     => '<path d="M5 20C5 10 11 4 21 3c0 10-6 17-16 17z"/><path d="M5 20c2-6 6-9 11-11"/>',
        'store'    => '<path d="M3 9l1.5-5h15L21 9"/><path d="M4 9v11h16V9"/><path d="M9 20v-6h6v6"/><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/>',
        'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'menu'     => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'eye'      => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'cursor'   => '<path d="M4 3l7 17 2.5-7.5L21 10z"/>',
        'trend'    => '<path d="M3 17l6-6 4 4 8-8"/><path d="M14 7h7v7"/>',
        'layout'   => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
        'layers'   => '<path d="M12 3l9 5-9 5-9-5z"/><path d="M3 13l9 5 9-5"/>',
        'chart'    => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'sliders'  => '<path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'file'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h6"/>',
        'mappin2'  => '<path d="M12 21s-7-6-7-11a7 7 0 0 1 14 0c0 5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'x'        => '<path d="M6 6l12 12M18 6L6 18"/>',
        'facebook' => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8.5A.5.5 0 0 1 14 8z"/>',
        'instagram'=> '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".6"/>',
        'twitter'  => '<path d="M22 5.8a8.5 8.5 0 0 1-2.4.7 4.2 4.2 0 0 0 1.8-2.3 8.4 8.4 0 0 1-2.7 1 4.2 4.2 0 0 0-7.2 3.8A12 12 0 0 1 2.8 4.6a4.2 4.2 0 0 0 1.3 5.6 4.2 4.2 0 0 1-1.9-.5 4.2 4.2 0 0 0 3.4 4.2 4.2 4.2 0 0 1-1.9.1 4.2 4.2 0 0 0 3.9 2.9A8.5 8.5 0 0 1 2 18.7 12 12 0 0 0 8.3 20.5c7.5 0 11.7-6.3 11.7-11.7v-.5A8.3 8.3 0 0 0 22 5.8z"/>',
        'linkedin' => '<path d="M4 9h4v11H4zM6 4a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM10 9h4v1.6C14.7 9.4 16 8.8 17.3 8.8 20 8.8 21 10.5 21 13.2V20h-4v-6c0-1.4-.4-2.3-1.6-2.3S14 12.700 14 14.100V20h-4z"/>',
    ];
    $inner = $p[$name] ?? $p['store'];
    $fill = in_array($name, ['star'], true) ? 'currentColor' : 'none';
    return '<svg class="ic ' . e($class) . '" width="' . (int)$size . '" height="' . (int)$size . '" viewBox="0 0 24 24" fill="' . $fill . '" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $inner . '</svg>';
}

/* Zimbabwe flag, small */
function flag_zw($w = 22) {
    $h = round($w * 0.62);
    return '<svg class="flag" width="' . $w . '" height="' . $h . '" viewBox="0 0 28 17" aria-hidden="true"><rect width="28" height="17" fill="#fff"/>' .
        '<rect y="0" width="28" height="2.43" fill="#00A651"/><rect y="2.43" width="28" height="2.43" fill="#FFD200"/><rect y="4.86" width="28" height="2.43" fill="#D40000"/>' .
        '<rect y="7.29" width="28" height="2.43" fill="#111"/><rect y="9.72" width="28" height="2.43" fill="#D40000"/><rect y="12.15" width="28" height="2.43" fill="#FFD200"/><rect y="14.58" width="28" height="2.42" fill="#00A651"/>' .
        '<path d="M0 0l12 8.5L0 17z" fill="#fff"/><path d="M0 1.5l9.6 7L0 15.5z" fill="#111"/><path d="M0 3l7.4 5.5L0 14z" fill="#D40000"/><circle cx="3.2" cy="8.5" r="1.4" fill="#FFD200"/></svg>';
}
