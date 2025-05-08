<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('Event_Grid_Module')) {
    class Event_Grid_Module extends ET_Builder_Module {
        public $slug       = 'event_grid';
        public $vb_support = 'off';
        public $icon       = 'eicon-posts-grid';

        function init() {
            $this->name             = esc_html__('Event Grid', 'event-grid-divi');
            $this->main_css_element = '%%order_class%%';

            $this->settings_modal_toggles = array(
                'general' => array(
                    'toggles' => array(
                        'filtering' => esc_html__('Filters', 'event-grid-divi'),
                        'layout'    => esc_html__('Layout', 'event-grid-divi'),
                    ),
                ),
                'advanced' => array(
                    'toggles' => array(
                        'category' => esc_html__('Category Style', 'event-grid-divi'),
                        'title'    => esc_html__('Title Style', 'event-grid-divi'),
                        'date'     => esc_html__('Date Style', 'event-grid-divi'),
                        'city'     => esc_html__('City Style', 'event-grid-divi'),
                        'image'    => esc_html__('Image Style', 'event-grid-divi'),
                    ),
                ),
            );
        }

        function get_fields() {
            return array(
                'filter_category' => array(
                    'label'       => esc_html__('Filter by Category', 'event-grid-divi'),
                    'type'        => 'multi_checkbox', // Utiliser multi-checkbox pour boutons
                    'options'     => $this->get_categories(), // Liste des catégories
                    'default'     => array(), // Par défaut, aucun filtre activé
                    'tab_slug'    => 'general',
                    'toggle_slug' => 'filtering',
                    'description' => esc_html__('Select categories to filter events.', 'event-grid-divi'),
                ),
                'filter_localisation' => array(
                    'label'       => esc_html__('Filter by Geozone', 'event-grid-divi'),
                    'type'        => 'multi_checkbox', // Utiliser multi-checkbox pour boutons
                    'options'     => $this->get_localisations(), // Liste des géozones
                    'default'     => array(), // Par défaut, aucun filtre activé
                    'tab_slug'    => 'general',
                    'toggle_slug' => 'filtering',
                    'description' => esc_html__('Select geozones to filter events.', 'event-grid-divi'),
                ),
                'columns' => array(
                    'label'         => esc_html__('Columns', 'event-grid-divi'),
                    'type'          => 'range',
                    'range_settings' => array(
                        'min'  => 1,
                        'max'  => 6,
                        'step' => 1,
                    ),
                    'default'       => 3,
                    'tab_slug'      => 'general',
                    'toggle_slug'   => 'layout',
                ),
                'grid_gap' => array(
                    'label'       => esc_html__('Grid Gap (e.g. 20px)', 'event-grid-divi'),
                    'type'        => 'text',
                    'default'     => '20px',
                    'tab_slug'    => 'general',
                    'toggle_slug' => 'layout',
                ),
            );
        }

        function get_advanced_fields_config() {
            return array(
                'fonts' => array(
                    'event_category' => array(
                        'label' => esc_html__('Category Text', 'event-grid-divi'),
                        'css'   => array('main' => '%%order_class%% .event-category'),
                        'tab_slug' => 'advanced',
                        'toggle_slug' => 'category',
                    ),
                    'event_title' => array(
                        'label' => esc_html__('Title Text', 'event-grid-divi'),
                        'css'   => array('main' => '%%order_class%% .event-title'),
                        'tab_slug' => 'advanced',
                        'toggle_slug' => 'title',
                    ),
                    'event_date' => array(
                        'label' => esc_html__('Date Text', 'event-grid-divi'),
                        'css'   => array('main' => '%%order_class%% .event-date'),
                        'tab_slug' => 'advanced',
                        'toggle_slug' => 'date',
                    ),
                    'event_city' => array(
                        'label' => esc_html__('City Text', 'event-grid-divi'),
                        'css'   => array('main' => '%%order_class%% .event-city'),
                        'tab_slug' => 'advanced',
                        'toggle_slug' => 'city',
                    ),
                ),
                'borders' => array(
                    'event_image' => array(
                        'label_prefix' => esc_html__('Image', 'event-grid-divi'),
                        'css'          => array('main' => '%%order_class%% .event-image img'),
                        'tab_slug'     => 'advanced',
                        'toggle_slug'  => 'image',
                    ),
                ),
                'margin_padding' => array(
                    'css' => array(
                        'main' => '%%order_class%% .event-item',
                    ),
                ),
            );
        }

        function render($attrs, $render_slug, $content = null) {
            return Event_Grid_Render::render_grid($attrs);
        }

        function apply_custom_css() {
            Event_Grid_Styles::apply_styles($this->render_slug, $this->props);
        }

        // === HELPERS ===

        private function get_categories() {
            $options = array();
            $terms = get_terms(array(
                'taxonomy' => 'evenements_category',
                'hide_empty' => false,
            ));

            if (!is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $options[$term->term_id] = $term->name;
                }
            }

            return $options;
        }

        private function get_localisations() {
            $options = array();
            $terms = get_terms(array(
                'taxonomy' => 'localisation',  // Taxonomie localisation
                'hide_empty' => false,
            ));

            if (!is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $options[$term->term_id] = $term->name;
                }
            }

            return $options;
        }
    }
}

if (!function_exists('register_event_grid_module')) {
    function register_event_grid_module() {
        if (class_exists('ET_Builder_Module')) {
            new Event_Grid_Module;
        }
    }
    add_action('et_builder_ready', 'register_event_grid_module');
}

