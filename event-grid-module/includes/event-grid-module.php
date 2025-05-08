<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('Event_Grid_Module')) {
    class Event_Grid_Module extends ET_Builder_Module {
        public $slug       = 'event_grid';
        public $vb_support = 'on'; // Activer explicitement la compatibilité Visual Builder
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
            // Récupération des slugs pour affichage
            $category_slugs = implode(', ', $this->get_category_slugs());
            $localisation_slugs = implode(', ', $this->get_localisation_slugs());

            return array(
                'filter_category' => array(
                    'label'       => esc_html__('Filter by Category IDs (comma-separated)', 'event-grid-divi'),
                    'type'        => 'text',
                    'default'     => '',
                    'tab_slug'    => 'general',
                    'toggle_slug' => 'filtering',
                    'description' => esc_html__('Enter category IDs separated by commas (e.g., "1,2,3").') 
                                     . '<br><strong>Available categories:</strong> ' . esc_html($category_slugs),
                ),
                'filter_localisation' => array(
                    'label'       => esc_html__('Filter by Geozone IDs (comma-separated)', 'event-grid-divi'),
                    'type'        => 'text',
                    'default'     => '',
                    'tab_slug'    => 'general',
                    'toggle_slug' => 'filtering',
                    'description' => esc_html__('Enter geozone IDs separated by commas (e.g., "4,5,6").') 
                                     . '<br><strong>Available geozones:</strong> ' . esc_html($localisation_slugs),
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
                    'event_image' => array(
                        'label_prefix' => esc_html__('Image', 'event-grid-divi'),
                        'css'          => array('main' => '%%order_class%% .event-image'),
                        'tab_slug'     => 'advanced',
                        'toggle_slug'  => 'image',
                    ),
                    'event_category' => array(
                        'label_prefix' => esc_html__('Category', 'event-grid-divi'),
                        'css'          => array('main' => '%%order_class%% .event-category'),
                        'tab_slug'     => 'advanced',
                        'toggle_slug'  => 'category',
                    ),
                    'event_title' => array(
                        'label_prefix' => esc_html__('Title', 'event-grid-divi'),
                        'css'          => array('main' => '%%order_class%% .event-title'),
                        'tab_slug'     => 'advanced',
                        'toggle_slug'  => 'title',
                    ),
                    'event_date' => array(
                        'label_prefix' => esc_html__('Date', 'event-grid-divi'),
                        'css'          => array('main' => '%%order_class%% .event-date'),
                        'tab_slug'     => 'advanced',
                        'toggle_slug'  => 'date',
                    ),
                    'event_city' => array(
                        'label_prefix' => esc_html__('City', 'event-grid-divi'),
                        'css'          => array('main' => '%%order_class%% .event-city'),
                        'tab_slug'     => 'advanced',
                        'toggle_slug'  => 'city',
                    ),
                ),
            );
        }

        function render($attrs, $render_slug, $content = null) {
            // Convertir les entrées en tableaux
            $filter_category = !empty($attrs['filter_category']) ? explode(',', $attrs['filter_category']) : array();
            $filter_geozone = !empty($attrs['filter_localisation']) ? explode(',', $attrs['filter_localisation']) : array();

            // Passer les attributs au rendu
            return Event_Grid_Render::render_grid(array(
                'filter_category' => $filter_category,
                'filter_geozone' => $filter_geozone,
                'columns' => isset($attrs['columns']) ? intval($attrs['columns']) : 3,
                'grid_gap' => isset($attrs['grid_gap']) ? $attrs['grid_gap'] : '20px',
            ));
        }

        private function get_category_slugs() {
            $slugs = array();
            $terms = get_terms(array(
                'taxonomy' => 'evenements_category',
                'hide_empty' => false,
            ));

            if (!is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $slugs[] = $term->slug;
                }
            }

            return $slugs;
        }

        private function get_localisation_slugs() {
            $slugs = array();
            $terms = get_terms(array(
                'taxonomy' => 'localisation',
                'hide_empty' => false,
            ));

            if (!is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $slugs[] = $term->slug;
                }
            }

            return $slugs;
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
