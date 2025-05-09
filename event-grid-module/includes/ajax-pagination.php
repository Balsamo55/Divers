<?php
if (!defined('ABSPATH')) exit; // Sécurité

function ajax_pagination_events() {
    // Vérifier le nonce pour la sécurité
    if (!check_ajax_referer('pagination_nonce', 'nonce', false)) {
        wp_send_json_error('Nonce invalide.');
        return;
    }

    // Log pour vérifier que la fonction est appelée
    error_log('AJAX Pagination Triggered');

    // Récupérer le numéro de page depuis les données AJAX
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $posts_per_page = 10; // Nombre d'événements par page

    // Construire la requête WP_Query
    $args = array(
        'post_type'      => 'evenements',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
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

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        ob_start(); // Capturer le contenu HTML
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

        // Log pour vérifier que les posts sont récupérés
        error_log('Posts récupérés pour la page ' . $paged);

        wp_send_json_success(array(
            'html' => $html,
        ));
    } else {
        // Log pour vérifier si aucun post n'est trouvé
        error_log('Aucun post trouvé pour la page ' . $paged);
        wp_send_json_error('Aucun événement trouvé.');
    }
}
add_action('wp_ajax_pagination_events', 'ajax_pagination_events');
add_action('wp_ajax_nopriv_pagination_events', 'ajax_pagination_events');