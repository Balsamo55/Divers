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
        }

        function get_fields() {
            return array(
                // Champs pour le filtrage
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
                // Champs pour la grille
                'columns' => array(
                    'label'         => esc_html__('Columns', 'event-grid-divi'),
                    'type'          => 'range',
                    'range_settings' => array(
                        'min'  => 1,
                        'max'  => 6,
                        'step' => 1,
                    ),
                    'default'       => 3,
                ),
                'grid_gap' => array(
                    'label'       => esc_html__('Grid Gap (e.g. 20px)', 'event-grid-divi'),
                    'type'        => 'text',
                    'default'     => '20px',
                ),
            );
        }

        function get_advanced_fields_config() {
            return array(
                // Configuration avancée pour le texte (sous-éléments)
                'fonts' => array(
                    'event_title' => array(
                        'label'    => esc_html__('Event Title Font', 'event-grid-divi'),
                        'css'      => array(
                            'main' => "%%order_class%% .event-title",
                        ),
                    ),
                    'event_category' => array(
                        'label'    => esc_html__('Event Category Font', 'event-grid-divi'),
                        'css'      => array(
                            'main' => "%%order_class%% .event-category",
                        ),
                    ),
                    'event_date' => array(
                        'label'    => esc_html__('Event Date Font', 'event-grid-divi'),
                        'css'      => array(
                            'main' => "%%order_class%% .event-date",
                        ),
                    ),
                    'event_city' => array(
                        'label'    => esc_html__('Event City Font', 'event-grid-divi'),
                        'css'      => array(
                            'main' => "%%order_class%% .event-city",
                        ),
                    ),
                ),
                // Configuration avancée pour les marges et espacements
                'margin_padding' => array(
                    'css' => array(
                        'main' => "%%order_class%% .event-item",
                        'important' => 'all',
                    ),
                ),
                // Configuration avancée pour l'image
                'borders' => array(
                    'image' => array(
                        'label'    => esc_html__('Image Border', 'event-grid-divi'),
                        'css'      => array(
                            'main' => "%%order_class%% .event-image",
                        ),
                    ),
                ),
            );
        }

        function render($attrs, $render_slug, $content = null) {
            // Transmettre les paramètres à la classe de rendu
            return Event_Grid_Render::render_grid(array(
                'filter_category' => !empty($attrs['filter_category']) ? explode(',', $attrs['filter_category']) : array(),
                'filter_localisation' => !empty($attrs['filter_localisation']) ? explode(',', $attrs['filter_localisation']) : array(),
                'columns'      => $attrs['columns'],
                'grid_gap'     => $attrs['grid_gap'],
            ));
        }
    }
}