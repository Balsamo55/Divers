<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('Event_Grid_Module')) {
    class Event_Grid_Module extends ET_Builder_Module {
        public $slug       = 'event_grid';
        public $vb_support = 'on';
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
                        'load_more_button' => esc_html__('Load More Button Style', 'event-grid-divi'),
                    ),
                ),
            );
        }

        function get_fields() {
            return array(
                'filter_category' => array(
                    'label'       => esc_html__('Filter by Category IDs (comma-separated)', 'event-grid-divi'),
                    'type'        => 'text',
                    'default'     => '',
                    'tab_slug'    => 'general',
                    'toggle_slug' => 'filtering',
                    'description' => esc_html__('Enter category IDs separated by commas (e.g., "1,2,3").'),
                ),
                'filter_localisation' => array(
                    'label'       => esc_html__('Filter by Geozone IDs (comma-separated)', 'event-grid-divi'),
                    'type'        => 'text',
                    'default'     => '',
                    'tab_slug'    => 'general',
                    'toggle_slug' => 'filtering',
                    'description' => esc_html__('Enter geozone IDs separated by commas (e.g., "4,5,6").'),
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
                'event_limit' => array(
                    'label'       => esc_html__('Maximum Number of Events', 'event-grid-divi'),
                    'type'        => 'range',
                    'range_settings' => array(
                        'min'  => 1,
                        'max'  => 50,
                        'step' => 1,
                    ),
                    'default'     => 10,
                    'tab_slug'    => 'general',
                    'toggle_slug' => 'layout',
                    'description' => esc_html__('Set the maximum number of events to display.'),
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
                    'load_more_button' => array(
                        'label' => esc_html__('Load More Button', 'event-grid-divi'),
                        'css'   => array(
                            'main'  => '%%order_class%% .load-more-button',
                            'hover' => '%%order_class%% .load-more-button:hover',
                        ),
                        'tab_slug' => 'advanced',
                        'toggle_slug' => 'load_more_button',
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
                    'load_more_button' => array(
                        'label_prefix' => esc_html__('Load More Button', 'event-grid-divi'),
                        'css'          => array('main' => '%%order_class%% .load-more-button'),
                        'tab_slug'     => 'advanced',
                        'toggle_slug'  => 'load_more_button',
                    ),
                ),
            );
        }

        function render($attrs, $render_slug, $content = null) {
            $filter_category = !empty($attrs['filter_category']) ? explode(',', $attrs['filter_category']) : array();
            $filter_geozone = !empty($attrs['filter_localisation']) ? explode(',', $attrs['filter_localisation']) : array();

            $event_limit = isset($attrs['event_limit']) ? intval($attrs['event_limit']) : 10;

            $output = Event_Grid_Render::render_grid(array(
                'filter_category' => $filter_category,
                'filter_geozone' => $filter_geozone,
                'columns' => isset($attrs['columns']) ? intval($attrs['columns']) : 3,
                'grid_gap' => isset($attrs['grid_gap']) ? $attrs['grid_gap'] : '20px',
                'event_limit' => $event_limit,
            ));

            if ($event_limit > 0) {
                $output .= sprintf(
                    '<button class="load-more-button" data-limit="%s" data-ajax-url="%s">%s</button>',
                    esc_attr($event_limit),
                    esc_url(admin_url('admin-ajax.php')),
                    esc_html__('Load More', 'event-grid-divi')
                );
            }

            return $output;
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