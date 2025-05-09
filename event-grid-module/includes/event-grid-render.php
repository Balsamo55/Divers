<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('Event_Grid_Render')) {
    class Event_Grid_Render {
        public static function render_grid($props) {
            // === Lecture des paramètres et valeurs par défaut ===
            $columns  = isset($props['columns']) ? intval($props['columns']) : 3;
            $grid_gap = isset($props['grid_gap']) ? $props['grid_gap'] : '20px';
            $filter_category = isset($props['filter_category']) ? $props['filter_category'] : [];
            $filter_geozone  = isset($props['filter_geozone']) ? $props['filter_geozone'] : [];
            $event_limit = isset($props['event_limit']) ? intval($props['event_limit']) : 10; // Limite par défaut à 10

            if (!is_array($filter_category)) {
                $filter_category = explode(',', $filter_category);
            }

            if (!is_array($filter_geozone)) {
                $filter_geozone = explode(',', $filter_geozone);
            }

            // CSS dynamique
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
            </style>';

            $output .= '<div class="event-grid">';

            // Construire la requête taxonomique
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
                'posts_per_page' => $event_limit, // Limite définie par l'utilisateur
                'meta_key'       => 'evenement_date_debut',
                'orderby'        => 'meta_value',
                'order'          => 'ASC',
                'meta_type'      => 'DATE',
                'meta_query'     => array(
                    array(
                        'key'     => 'evenement_date_debut',
                        'value'   => date('Y-m-d'),
                        'compare' => '>=',
                        'type'    => 'DATE',
                    ),
                ),
            );

            if (count($tax_query) > 1) {
                $args['tax_query'] = $tax_query;
            }

            $posts = get_posts($args);

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
                    $permalink  = get_permalink($post->ID);

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

                    // Fallback global si aucune image définie
                    if (empty($image)) {
                        $fallback_url = plugin_dir_url(__FILE__) . '../assets/fallback-image.jpg';
                        $image = sprintf(
                            '<img src="%s" alt="%s" loading="lazy" decoding="async" class="event-image-fallback" />',
                            esc_url($fallback_url),
                            esc_attr('Image par défaut')
                        );
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

            // Ajouter la pagination
            $total_posts = wp_count_posts('evenements')->publish;
            $total_pages = ceil($total_posts / $event_limit);

            if ($total_pages > 1) {
                $output .= '<div class="pagination">';
                for ($i = 1; $i <= $total_pages; $i++) {
                    $output .= sprintf(
                        '<a href="#" class="pagination-link" data-page="%d" data-ajax-url="%s" data-nonce="%s">%d</a>',
                        $i,
                        esc_url(admin_url('admin-ajax.php')),
                        esc_attr(wp_create_nonce('pagination_nonce')),
                        $i
                    );
                }
                $output .= '</div>';
            }

            return $output;
        }
    }
}
