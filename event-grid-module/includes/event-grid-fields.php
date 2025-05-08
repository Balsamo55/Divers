<?php

if (!defined('ABSPATH')) exit;

class Event_Grid_Fields {
    public static function get_fields() {
        return array(
            // === Champs pour la disposition de la grille ===
            'columns' => array(
                'label'           => esc_html__('Columns', 'event-grid-divi'),
                'type'            => 'range',
                'default'         => '3',
                'range_settings'  => array(
                    'min'  => '1',
                    'max'  => '6',
                    'step' => '1',
                ),
                'tab_slug'        => 'content',
                'toggle_slug'     => 'layout',
            ),
            'grid_gap' => array(
                'label'           => esc_html__('Grid Gap', 'event-grid-divi'),
                'type'            => 'text',
                'default'         => '20px',
                'tab_slug'        => 'content',
                'toggle_slug'     => 'layout',
            ),

            // === Champs pour la marge inférieure ===
            'event_image_margin_bottom' => array(
                'label'           => esc_html__('Image Bottom Margin', 'event-grid-divi'),
                'type'            => 'text',
                'tab_slug'        => 'design',
                'toggle_slug'     => 'image',
                'default'         => '10px',
            ),
            'event_title_margin_bottom' => array(
                'label'           => esc_html__('Title Bottom Margin', 'event-grid-divi'),
                'type'            => 'text',
                'tab_slug'        => 'design',
                'toggle_slug'     => 'text',
                'default'         => '10px',
            ),
            'event_category_margin_bottom' => array(
                'label'           => esc_html__('Category Bottom Margin', 'event-grid-divi'),
                'type'            => 'text',
                'tab_slug'        => 'design',
                'toggle_slug'     => 'category',
                'default'         => '10px',
            ),
            'event_date_margin_bottom' => array(
                'label'           => esc_html__('Date Bottom Margin', 'event-grid-divi'),
                'type'            => 'text',
                'tab_slug'        => 'design',
                'toggle_slug'     => 'date',
                'default'         => '10px',
            ),
            'event_city_margin_bottom' => array(
                'label'           => esc_html__('City Bottom Margin', 'event-grid-divi'),
                'type'            => 'text',
                'tab_slug'        => 'design',
                'toggle_slug'     => 'city',
                'default'         => '10px',
            ),
        );
    }
}