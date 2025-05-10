<?php
/**
 * Plugin Name: Event Grid for Divi
 * Description: Module Divi + shortcode pour afficher la grille d’événements.
 * Version: 1.0
 * Author: Ton Nom
 */

if (!defined('ABSPATH')) exit;

// 0) Charger le renderer et les styles (nécessaires pour le shortcode et l’AJAX)
require_once plugin_dir_path(__FILE__) . 'includes/event-grid-render.php';
require_once plugin_dir_path(__FILE__) . 'includes/event-grid-styles.php';

// 1) Enregistrer le shortcode [event_grid] en front-end
add_action('init', function(){
    add_shortcode('event_grid', ['Event_Grid_Render', 'render_grid']);
});

// 2) Enregistrer le module Divi après que Divi Builder soit prêt
add_action('et_builder_ready', function() {
    if (class_exists('ET_Builder_Module')) {
        require_once plugin_dir_path(__FILE__) . 'includes/event-grid-module.php';
        new Event_Grid_Module();
    }
});

// 3) Désenregistrer les scripts conflictuels (priorité 9)
add_action('wp_enqueue_scripts', function() {
    wp_dequeue_script('divi-filter-loadmore');
    wp_deregister_script('divi-filter-loadmore');
    wp_dequeue_script('pagination-js');
    wp_deregister_script('pagination-js');
}, 9);

// 4) Enqueue du style global
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'event-grid-plugin-style',
        plugin_dir_url(__FILE__) . 'css/event-grid.css',
        [],
        '1.0'
    );
});

// 5) Enqueue du script Load More + localisation des variables AJAX (priorité 10)
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'event-load-more',
        plugin_dir_url(__FILE__) . 'js/load-more.js',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'js/load-more.js'),
        true
    );
    wp_localize_script(
        'event-load-more',
        'EventGridData',
        [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('load_more_nonce'),
        ]
    );
}, 10);

// 6) Hooks AJAX pour Load More
add_action('wp_ajax_load_more_events',    ['Event_Grid_Render', 'handle_load_more']);
add_action('wp_ajax_nopriv_load_more_events', ['Event_Grid_Render', 'handle_load_more']);
