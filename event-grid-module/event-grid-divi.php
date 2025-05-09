<?php
/*
Plugin Name: Event Grid for Divi
Description: Module Divi pour afficher les événements.
Version: 1.0
Author: Ton Nom
*/

if (!defined('ABSPATH')) exit;

function register_event_grid_module() {
    if (class_exists('ET_Builder_Module')) {

        // Inclure tous les composants
        include_once plugin_dir_path(__FILE__) . 'includes/event-grid-module.php';
        include_once plugin_dir_path(__FILE__) . 'includes/event-grid-render.php';
        include_once plugin_dir_path(__FILE__) . 'includes/event-grid-styles.php';

        // Enregistrer le module Divi
        new Event_Grid_Module;
    }
}
add_action('et_builder_ready', 'register_event_grid_module');

// Enregistrer les styles et scripts
function enqueue_event_grid_assets() {
    wp_enqueue_style(
        'event-grid-plugin-style',
        plugin_dir_url(__FILE__) . 'css/event-grid.css',
        array(),
        '1.0'
    );

    wp_enqueue_script(
        'event-grid-plugin-script',
        plugin_dir_url(__FILE__) . 'js/event-grid.js',
        array('jquery'),
        '1.0',
        true
    );

    // Passer l'URL AJAX au script
    wp_localize_script('event-grid-plugin-script', 'eventGridAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_event_grid_assets');

function enqueue_pagination_scripts() {
    wp_enqueue_script(
        'pagination-js',
        plugin_dir_url(__FILE__) . 'js/pagination.js',
        array('jquery'),
        '1.0',
        true
    );
    wp_localize_script('pagination-js', 'paginationData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('pagination_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_pagination_scripts');