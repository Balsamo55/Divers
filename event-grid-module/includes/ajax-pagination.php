<?php
if (!defined('ABSPATH')) exit;

function ajax_pagination_events() {
    if (!check_ajax_referer('pagination_nonce', 'nonce', false)) {
        wp_send_json_error('Nonce invalide.');
        return;
    }

    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $posts_per_page = 10;

    $args = [
        'post_type' => 'evenements',
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'meta_key' => 'evenement_date_debut',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'meta_type' => 'DATE',
        'meta_query' => [
            [
                'key' => 'evenement_date_debut',
                'value' => date('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE',
            ],
        ],
    ];

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <a href="<?php the_permalink(); ?>" class="event-item">
                <div class="event-image"><?php the_post_thumbnail('medium'); ?></div>
                <div class="event-title"><?php the_title(); ?></div>
                <div class="event-date"><?php the_field('evenement_date_debut'); ?></div>
            </a>
            <?php
        }
        wp_reset_postdata();
        $html = ob_get_clean();
        // --- après ob_get_clean() ---
$total_pages = $query->max_num_pages;
$pagination   = '';

if ( $total_pages > 1 ) {
    $pagination .= '<div class="pagination">';
    for ( $i = 1; $i <= $total_pages; $i++ ) {
        $pagination .= sprintf(
            '<a href="?paged=%d" class="pagination-link" data-page="%d" data-ajax-url="%s" data-nonce="%s">%d</a>',
            $i,
            $i,
            esc_url( admin_url('admin-ajax.php') ),
            esc_attr( wp_create_nonce('pagination_nonce') ),
            $i
        );
    }
    $pagination .= '</div>';
}

// Renvoi l’HTML des events + la pagination
wp_send_json_success([
    'html'       => $html,
    'pagination' => $pagination,
]);

        wp_send_json_success(['html' => $html]);
    } else {
        wp_send_json_error('Aucun événement trouvé.');
    }
}
add_action('wp_ajax_pagination_events', 'ajax_pagination_events');
add_action('wp_ajax_nopriv_pagination_events', 'ajax_pagination_events');