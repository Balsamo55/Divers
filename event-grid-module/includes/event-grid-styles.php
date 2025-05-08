<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('Event_Grid_Styles')) {
    class Event_Grid_Styles {
        public static function apply_styles($render_slug, $props) {
            $columns  = isset($props['columns']) ? intval($props['columns']) : 3;
            $grid_gap = isset($props['grid_gap']) ? $props['grid_gap'] : '20px';

            ET_Builder_Element::set_style(
                $render_slug,
                array(
                    'selector'    => '%%order_class%% .event-grid',
                    'declaration' => sprintf(
                        'display: grid; grid-template-columns: repeat(%d, 1fr); gap: %s;',
                        $columns,
                        esc_attr($grid_gap)
                    ),
                )
            );
        }
    }
}
