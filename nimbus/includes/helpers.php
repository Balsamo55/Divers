<?php
if (!defined('ABSPATH')) exit;

// Helpers UX : couleur du score
function nimbus_score_color($score) {
    if ($score >= 90) return 'ok';
    if ($score >= 50) return 'warn';
    return 'bad';
}
// Helpers UX : description pédagogique
function nimbus_score_help($label) {
    switch ($label) {
        case 'Performance':
            return "👉 Rapidité d’affichage, images, scripts, cache…";
        case 'Accessibilité':
            return "👉 Contraste, alt, navigation clavier…";
        case 'Bonnes pratiques':
            return "👉 Sécurité, code fiable…";
        case 'SEO':
            return "👉 Structure, meta, indexabilité…";
        default:
            return '';
    }
}

// Helper : détecter plugins actifs (Divi, WP Rocket, Imagify)
if (!function_exists('is_plugin_active')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}