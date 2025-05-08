<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('Event_Grid_Styles')) {
    class Event_Grid_Styles {
        public static function apply_styles($render_slug, $props) {
            // === Styles pour la grille principale ===
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

            // === Styles dynamiques pour chaque sous-élément ===
            $elements = array(
                'event-image'    => 'event_image_margin_bottom',
                'event-title'    => 'event_title_margin_bottom',
                'event-category' => 'event_category_margin_bottom',
                'event-date'     => 'event_date_margin_bottom',
                'event-city'     => 'event_city_margin_bottom',
            );

            foreach ($elements as $class => $prop) {
                if (!empty($props[$prop])) {
                    ET_Builder_Element::set_style(
                        $render_slug,
                        array(
                            'selector'    => sprintf('%%order_class%% .%s', $class),
                            'declaration' => sprintf(
                                'margin-bottom: %s;',
                                esc_attr($props[$prop])
                            ),
                        )
                    );
                }
            }
        }
    }
}