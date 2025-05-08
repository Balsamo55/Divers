<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('Event_Grid_Render')) {
    class Event_Grid_Render {
        public static function render_grid($props) {
            // === Débogage : Afficher les paramètres transmis ===
            error_log('Event Grid Render - Props: ' . print_r($props, true));

            // === Lecture des paramètres et valeurs par défaut ===
            $columns  = isset($props['columns']) ? intval($props['columns']) : 3;
            $grid_gap = isset($props['grid_gap']) ? $props['grid_gap'] : '20px';
            $filter_category = isset($props['filter_category']) ? $props['filter_category'] : [];
            $filter_geozone  = isset($props['filter_geozone']) ? $props['filter_geozone'] : [];

            // Vérifier les paramètres critiques
            if (!is_array($filter_category)) {
                $filter_category = explode(',', $filter_category); // Convertir en tableau si nécessaire
            }

            if (!is_array($filter_geozone)) {
                $filter_geozone = explode(',', $filter_geozone); // Convertir en tableau si nécessaire
            }

            // === Débogage : Vérifier les valeurs des colonnes et des filtres ===
            error_log('Event Grid Render - Columns: ' . $columns);
            error_log('Event Grid Render - Grid Gap: ' . $grid_gap);
            error_log('Event Grid Render - Filter Category: ' . print_r($filter_category, true));
            error_log('Event Grid Render - Filter Geozone: ' . print_r($filter_geozone, true));

            // === Récupération des styles personnalisés ===
            $image_style    = isset($props['event_image_style']) ? $props['event_image_style'] : '';
            $category_style = isset($props['event_category_style']) ? $props['event_category_style'] : '';
            $title_style    = isset($props['event_title_style']) ? $props['event_title_style'] : '';
            $date_style     = isset($props['event_date_style']) ? $props['event_date_style'] : '';
            $city_style     = isset($props['event_city_style']) ? $props['event_city_style'] : '';

            // === Récupération des marges et paddings ===
            $image_margin_padding    = isset($props['event_image_margin_padding']) ? $props['event_image_margin_padding'] : '';
            $category_margin_padding = isset($props['event_category_margin_padding']) ? $props['event_category_margin_padding'] : '';
            $title_margin_padding    = isset($props['event_title_margin_padding']) ? $props['event_title_margin_padding'] : '';
            $date_margin_padding     = isset($props['event_date_margin_padding']) ? $props['event_date_margin_padding'] : '';
            $city_margin_padding     = isset($props['event_city_margin_padding']) ? $props['event_city_margin_padding'] : '';

            // === Génération du CSS dynamique ===
            $output = '<style>
                .event-grid {
                    display: grid;
                    grid-template-columns: repeat(' . $columns . ', 1fr);
                    gap: ' . esc_attr($grid_gap) . ';
                }
                .event-grid img {
                    width: 100%;
                    height: 200px;
                    object-fit: cover;
                    display: block;
                }
                .event-grid .event-image {' . esc_attr($image_style) . esc_attr($image_margin_padding) . '}
                .event-grid .event-category {' . esc_attr($category_style) . esc_attr($category_margin_padding) . '}
                .event-grid .event-title {' . esc_attr($title_style) . esc_attr($title_margin_padding) . '}
                .event-grid .event-date {' . esc_attr($date_style) . esc_attr($date_margin_padding) . '}
                .event-grid .event-city {' . esc_attr($city_style) . esc_attr($city_margin_padding) . '}
            </style>';

            $output .= '<div class="event-grid">';

            // === Construire la requête taxonomique ===
            $tax_query = array('relation' => 'AND');

            if (!empty($filter_category)) {
                $tax_query[] = array(
                    'taxonomy' => 'evenements_category',
                    'field'    => 'term_id',
                    'terms'    => $filter_category,
                );
            }

            if (!empty($filter_geozone)) {
                $tax_query[] = array(
                    'taxonomy' => 'relation_geozone',
                    'field'    => 'term_id',
                    'terms'    => $filter_geozone,
                );
            }

            $args = array(
                'post_type'      => 'evenements',
                'posts_per_page' => -1,
                'meta_key'       => 'evenement_date_debut', // Trier par date de début
                'orderby'        => 'meta_value',
                'order'          => 'ASC',
                'meta_type'      => 'DATE',
                'meta_query'     => array(
                    array(
                        'key'     => 'evenement_date_debut',
                        'value'   => date('Y-m-d'), // Exclure les événements passés
                        'compare' => '>=',
                        'type'    => 'DATE',
                    ),
                ),
            );

            if (count($tax_query) > 1) {
                $args['tax_query'] = $tax_query;
            }

            // === Débogage : Vérifier les arguments passés à get_posts ===
            error_log('Event Grid Render - WP_Query Args: ' . print_r($args, true));

            $posts = get_posts($args);

            // === Rendu des événements ===
            if (empty($posts)) {
                $output .= '<div class="debug">❌ Aucun post trouvé - Vérifiez vos filtres.</div>';
            } else {
                foreach ($posts as $post) {
                    setup_postdata($post);

                    $title      = get_the_title($post);
                    $image      = get_the_post_thumbnail($post->ID, 'medium');
                    $categories = get_the_terms($post->ID, 'evenements_category');
                    $date       = get_field('evenement_date_debut', $post->ID);
                    $ville      = get_field('evenement_ville', $post->ID);
                    $permalink  = get_permalink($post->ID); // Lien vers l'événement

                    // Fallback pour l'image de catégorie
                    if (empty($image) && !empty($categories) && !is_wp_error($categories)) {
                        $term     = $categories[0];
                        $image_id = get_term_meta($term->term_id, 'evenements_category_image', true);
                        if ($image_id) {
                            $img_url = wp_get_attachment_image_url($image_id, 'medium');
                            $alt     = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                            $image   = sprintf(
                                '<img src="%s" alt="%s" loading="lazy" decoding="async" class="event-image-fallback" />',
                                esc_url($img_url),
                                esc_attr($alt ? $alt : $title)
                            );
                        }
                    }

                    $category_names = '';
                    if (!is_wp_error($categories) && !empty($categories)) {
                        $category_names = join(', ', wp_list_pluck($categories, 'name'));
                    }

                    $output .= '<a href="' . esc_url($permalink) . '" class="event-item">';
                    if ($image) {
                        $output .= '<div class="event-image">' . $image . '</div>';
                    }
                    if ($category_names) {
                        $output .= '<div class="event-category">' . esc_html($category_names) . '</div>';
                    }
                    $output .= '<div class="event-title">' . esc_html($title) . '</div>';
                    if ($date) {
                        $output .= '<div class="event-date">' . esc_html($date) . '</div>';
                    }
                    if ($ville) {
                        $output .= '<div class="event-city">' . esc_html($ville) . '</div>';
                    }
                    $output .= '</a>';
                }
                wp_reset_postdata();
            }

            $output .= '</div>';
            return $output;
        }
    }
}