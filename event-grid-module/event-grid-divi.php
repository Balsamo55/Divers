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

function enqueue_event_grid_styles() {
    wp_enqueue_style(
        'event-grid-plugin-style',
        plugin_dir_url(__FILE__) . 'css/event-grid.css',
        array(),
        '1.0'
    );
}
add_action('wp_enqueue_scripts', 'enqueue_event_grid_styles');