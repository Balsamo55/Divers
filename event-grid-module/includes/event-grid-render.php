<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('Event_Grid_Render')) {
    class Event_Grid_Render {
        /**
         * AJAX handler pour charger les événements supplémentaires.
         */
        public static function handle_load_more() {
            // Vérification du nonce
            check_ajax_referer('load_more_nonce', 'nonce');

            $paged       = isset($_POST['page']) ? absint($_POST['page']) : 1;
            $event_limit = isset($_POST['event_limit']) ? absint($_POST['event_limit']) : 10;

            // Requête pour la page demandée
            $args = [
                'post_type'      => 'evenements',
                'posts_per_page' => $event_limit,
                'paged'          => $paged,
                'meta_key'       => 'evenement_date_debut',
                'orderby'        => 'meta_value',
                'order'          => 'ASC',
                'meta_type'      => 'DATE',
                'meta_query'     => [[
                    'key'     => 'evenement_date_debut',
                    'value'   => date('Y-m-d'),
                    'compare' => '>=',
                    'type'    => 'DATE',
                ]],
            ];

            $query = new WP_Query($args);

            // Générer le HTML des items
            ob_start();
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    $title      = get_the_title();
                    $permalink  = get_permalink();
                    $date       = get_field('evenement_date_debut');
                    $ville      = get_field('evenement_ville');
                    $categories = get_the_terms(get_the_ID(), 'evenements_category');

                    // Image ou fallback
                    $image_html = get_the_post_thumbnail(get_the_ID(), 'medium');
                    if (!$image_html && $categories && !is_wp_error($categories)) {
                        $term   = $categories[0];
                        $img_id = get_term_meta($term->term_id, 'evenements_category_image', true);
                        if ($img_id) {
                            $url = wp_get_attachment_image_url($img_id, 'medium');
                            $alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                            $image_html = '<img src="'.esc_url($url).'" alt="'.esc_attr($alt?:$title).'" loading="lazy" class="event-image-fallback"/>';
                        }
                    }
                    if (!$image_html) {
                        $fallback = plugin_dir_url(__FILE__) . '../assets/fallback-image.jpg';
                        $image_html = '<img src="'.esc_url($fallback).'" alt="Image par défaut" loading="lazy" class="event-image-fallback"/>';
                    }

                    // Noms de catégories
                    $category_names = '';
                    if ($categories && !is_wp_error($categories)) {
                        $category_names = implode(', ', wp_list_pluck($categories, 'name'));
                    }

                    // Construction de l'item dans l'ordre voulu
                    echo '<a href="'.esc_url($permalink).'" class="event-item">';
                    // 1. Image
                    echo '<div class="event-image">'.$image_html.'</div>';
                    // 2. Catégorie
                    if ($category_names) {
                        echo '<div class="event-category">'.esc_html($category_names).'</div>';
                    }
                    // 3. Titre
                    echo '<div class="event-title">'.esc_html($title).'</div>';
                    // 4. Date
                    if ($date) {
                        echo '<div class="event-date">'.esc_html($date).'</div>';
                    }
                    // 5. Ville
                    if ($ville) {
                        echo '<div class="event-city">'.esc_html($ville).'</div>';
                    }
                    echo '</a>';
                }
                wp_reset_postdata();
            }
            $items_html = ob_get_clean();

            // Pagination data
            $next_page = $paged + 1;
            $max_pages = $query->max_num_pages;

            wp_send_json_success([
                'html'      => $items_html,
                'next_page' => $next_page,
                'max_pages' => $max_pages,
            ]);
        }

        /**
         * Affiche la grille d'événements et le bouton Load More.
         * @param array $props Attributs passés (columns, grid_gap, filter_category, filter_geozone, event_limit)
         * @return string HTML de la grille + bouton
         */
        public static function render_grid($props) {
            $columns         = isset($props['columns']) ? intval($props['columns']) : 3;
            $grid_gap        = isset($props['grid_gap'])  ? esc_attr($props['grid_gap'])  : '20px';
            $filter_category = !empty($props['filter_category']) ? (array) $props['filter_category'] : [];
            $filter_geozone  = !empty($props['filter_geozone'])  ? (array) $props['filter_geozone']  : [];
            $event_limit     = isset($props['event_limit'])     ? intval($props['event_limit'])     : 10;

            $tax_query = ['relation' => 'AND'];
            if ($filter_category) {
                $tax_query[] = ['taxonomy'=>'evenements_category','field'=>'term_id','terms'=>$filter_category];
            }
            if ($filter_geozone) {
                $tax_query[] = ['taxonomy'=>'relation_geozone','field'=>'term_id','terms'=>$filter_geozone];
            }

            $paged = 1;
            $args  = ['post_type'=>'evenements','posts_per_page'=>$event_limit,'paged'=>$paged,'meta_key'=>'evenement_date_debut','orderby'=>'meta_value','order'=>'ASC','meta_type'=>'DATE','meta_query'=>[['key'=>'evenement_date_debut','value'=>date('Y-m-d'),'compare'=>'>=','type'=>'DATE']]];
            if (count($tax_query)>1) $args['tax_query']=$tax_query;
            $query = new WP_Query($args);

            // Ouvre la grille avec vars CSS
            $output = '<div class="event-grid" style="--eg-columns:'.$columns.';--eg-gap:'.$grid_gap.';">';

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $title      = get_the_title();
                    $permalink  = get_permalink();
                    $date       = get_field('evenement_date_debut');
                    $ville      = get_field('evenement_ville');
                    $categories = get_the_terms(get_the_ID(),'evenements_category');

                    // Image ou fallback
                    $image_html = get_the_post_thumbnail(get_the_ID(),'medium');
                    if (!$image_html && $categories && !is_wp_error($categories)) {
                        $term   = $categories[0];
                        $img_id = get_term_meta($term->term_id,'evenements_category_image',true);
                        if ($img_id) {
                            $url = wp_get_attachment_image_url($img_id,'medium');
                            $alt = get_post_meta($img_id,'_wp_attachment_image_alt',true);
                            $image_html = '<img src="'.esc_url($url).'" alt="'.esc_attr($alt?:$title).'" loading="lazy" class="event-image-fallback"/>';
                        }
                    }
                    if (!$image_html) {
                        $fallback = plugin_dir_url(__FILE__).'../assets/fallback-image.jpg';
                        $image_html = '<img src="'.esc_url($fallback).'" alt="Image par défaut" loading="lazy" class="event-image-fallback"/>';
                    }

                    $category_names = '';
                    if ($categories && !is_wp_error($categories)) {
                        $category_names = implode(', ', wp_list_pluck($categories,'name'));
                    }

                    // Construction de l'item
                    $output .= '<a href="'.esc_url($permalink).'" class="event-item">';
                    // 1. Image
                    $output .= '<div class="event-image">'.$image_html.'</div>';
                    // 2. Catégorie
                    if ($category_names) {
                        $output .= '<div class="event-category">'.esc_html($category_names).'</div>';
                    }
                    // 3. Titre
                    $output .= '<div class="event-title">'.esc_html($title).'</div>';
                    // 4. Date
                    if ($date) {
                        $output .= '<div class="event-date">'.esc_html($date).'</div>';
                    }
                    // 5. Ville
                    if ($ville) {
                        $output .= '<div class="event-city">'.esc_html($ville).'</div>';
                    }
                    $output .= '</a>';
                }
                wp_reset_postdata();
            }
            $output .= '</div>';

            $max_pages = $query->max_num_pages;
            if ($paged < $max_pages) {
                $output .= sprintf('<button class="load-more-button" data-page="%1$d" data-max-pages="%2$d" data-ajax-url="%3$s" data-nonce="%4$s">Charger plus</button>',
                    $paged+1,
                    $max_pages,
                    esc_url(admin_url('admin-ajax.php')),
                    esc_attr(wp_create_nonce('load_more_nonce'))
                );
            }

            return $output;
        }
    }
}
