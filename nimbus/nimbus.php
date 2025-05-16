<?php
/*
Plugin Name: Nimbus – Audit & Correcteur PageSpeed
Description: Analyse, corrige et explique les points Google PageSpeed (Performance, Accessibilité, Bonnes Pratiques, SEO). Compatible Divi, WP Rocket, Imagify – Expérience UX et pédagogique.
Version: 1.0
Author: Les Idées Fixes
Author URI: https://lesideesfixes.fr
*/

if (!defined('ABSPATH')) exit;

// Chargement des modules
foreach ([
    'helpers.php',
    'audit.php',
    'audit-details.php',
    'correctifs.php',
    'modules-unused.php',
    'accessibilite.php',
    'seo.php',
    'historique.php',
    'notifications.php',
    'settings.php'
] as $file) {
    require_once plugin_dir_path(__FILE__).'includes/'.$file;
}

// Chargement styles UX admin globaux
add_action('admin_head', function() {
    ?>
    <style>
    .nimbus-admin .nimbus-sections { display:flex; gap:2rem; margin:1.5em 0;}
    .nimbus-admin .nimbus-section { background:#f8faff;border-radius:8px;padding:1em;flex:1;min-width:220px;}
    .nimbus-admin .nimbus-score-placeholder { background:#eaf1fb;color:#aaa;padding:1.2em;border-radius:4px;text-align:center;margin-bottom:.5em;}
    .nimbus-admin .nimbus-note { color:#4893f5;font-size:0.95em; }
    .nimbus-admin .nimbus-module-columns { display:flex; gap:3em;}
    .nimbus-admin .nimbus-module-columns > div { flex:1;}
    .nimbus-admin .nimbus-help {margin-top:2em;background:#f2f8ed;padding:1em;border-radius:6px;}
    .nimbus-scores { display:flex;flex-wrap:wrap;gap:2em;}
    .nimbus-scores li { background:#fafafa; border-radius:8px; padding:1.2em 2em; margin:1em 0; min-width:220px; flex:1; box-shadow:0 1px 3px #0001;}
    .nimbus-score-label { font-weight:600; font-size:1.1em;}
    .nimbus-score-value { font-size:1.7em; font-weight:700; margin-left:.4em;}
    .nimbus-score-ok { color:#24b624;}
    .nimbus-score-warn { color:#e6b800;}
    .nimbus-score-bad { color:#d22;}
    .nimbus-score-help { display:block; font-size:.98em; color:#888; margin-top:.4em;}
    </style>
    <?php
});