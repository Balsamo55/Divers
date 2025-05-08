<?php

if (!defined('ABSPATH')) exit;

class Event_Grid_Fields {
    public static function get_fields() {
        return array(
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
        );
    }
}
