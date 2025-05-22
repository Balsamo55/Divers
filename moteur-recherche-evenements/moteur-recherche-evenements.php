<?php
/**
 * Plugin Name: Moteur Recherche Evenements
 * Description: Moteur de recherche AJAX avancé pour le CTP "evenements" avec filtres ACF (dates, ville, catégorie, etc.)
 * Version: 1.1
 * Author: Ton Nom
 */

define('MRE_PATH', plugin_dir_path(__FILE__));
define('MRE_URL', plugin_dir_url(__FILE__));

// Chargement scripts et styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('jquery-ui-autocomplete');
    wp_enqueue_script('mre-script', MRE_URL . 'assets/js/mre.js', ['jquery', 'jquery-ui-autocomplete'], null, true);
    wp_localize_script('mre-script', 'mre_ajax', ['url' => admin_url('admin-ajax.php')]);

    wp_enqueue_style('mre-style', MRE_URL . 'assets/css/mre.css');

// Flatpickr (calendrier custom)
wp_enqueue_style('flatpickr-style', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
wp_enqueue_script('flatpickr-locale-fr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js', ['flatpickr'], null, true);
    
});

// Shortcode pour afficher le formulaire
add_shortcode('moteur_recherche_evenements', 'mre_afficher_formulaire');
function mre_afficher_formulaire($atts = []) {
    $atts = shortcode_atts([
        'output_target' => '#mre-resultats'
    ], $atts);

    $villes = [
    // Grand Est
    'Strasbourg', 'Reims', 'Metz', 'Mulhouse', 'Nancy', 'Colmar', 'Troyes', 'Charleville-Mézières',
    'Châlons-en-Champagne', 'Thionville', 'Haguenau', 'Schiltigheim', 'Épinal', 'Vandœuvre-lès-Nancy',
    'Illkirch-Graffenstaden', 'Saint-Dizier', 'Saint-Louis', 'Épernay', 'Montigny-lès-Metz', 'Chaumont',
    'Forbach', 'Bar-le-Duc', 'Sélestat', 'Saverne', 'Wissembourg', 'Sarrebourg', 'Saint-Avold',
    'Verdun', 'Toul', 'Vitry-le-François', 'Rethel', 'Sedan', 'Commercy', 'Lunéville', 'Neufchâteau',
    'Sarre-Union', 'Bitche', 'Pont-à-Mousson', 'Longwy', 'Hayange',

    // Bourgogne-Franche-Comté
    'Dijon', 'Besançon', 'Belfort', 'Chalon-sur-Saône', 'Auxerre', 'Mâcon', 'Nevers', 'Sens',
    'Montbéliard', 'Dole', 'Le Creusot', 'Beaune', 'Pontarlier', 'Montceau-les-Mines',
    'Lons-le-Saunier', 'Vesoul', 'Chenôve', 'Audincourt', 'Autun', 'Avallon', 'Gray', 'Lure',
    'Champagnole', 'Saint-Claude', 'Clamecy', 'Cosne-Cours-sur-Loire', 'Charolles', 'Louhans',
    'Cluny', 'Paray-le-Monial', 'Tournus', 'Joigny', 'Tonnerre', 'Château-Chinon', 'Decize',
    'La Charité-sur-Loire', 'Semur-en-Auxois', 'Nuits-Saint-Georges', 'Is-sur-Tille',
    'Chevigny-Saint-Sauveur'
];

    $categories = get_terms([
        'taxonomy' => 'evenements_category',
        'hide_empty' => false,
    ]);

    $category_labels = array_map(function($term) {
        return $term->name;
    }, $categories);

    ob_start(); ?>
    <form id="mre-form" data-output="<?php echo esc_attr($atts['output_target']); ?>">
        <label for="recherche">Titre :</label>
        <input type="text" name="recherche" id="recherche" placeholder="Titre de l'événement" aria-label="Titre">

        <label for="ville">Ville :</label>
        <input type="text" name="ville" id="ville" placeholder="Commencez à taper une ville…" autocomplete="off" aria-label="Ville">
        <ul id="ville-suggestions" class="mre-suggestions"></ul>

        <label for="date_debut">Du :</label>
        <input type="date" name="date_debut" id="date_debut" aria-label="Début">

        <label for="date_fin">Au :</label>
        <input type="date" name="date_fin" id="date_fin" aria-label="Fin">

        <label for="categorie">Catégorie :</label>
        <input type="text" name="categorie" id="categorie" placeholder="Catégorie…" autocomplete="off" aria-label="Catégorie">
        <ul id="categorie-suggestions" class="mre-suggestions"></ul>

        <button type="submit">Filtrer</button>
        <button type="reset" id="mre-reset" aria-label="Réinitialiser les filtres">✕</button>
    </form>
    <script>
        window.mreVilles = <?php echo json_encode($villes); ?>;
        window.mreCategories = <?php echo json_encode($category_labels); ?>;
    </script>
    <?php
    return ob_get_clean();
}

// Shortcode grille d'événements
add_shortcode('grille_evenements', 'mre_grille_evenements');
function mre_grille_evenements($atts = []) {
    $atts = shortcode_atts([
        'id' => 'mre-resultats'
    ], $atts);

    ob_start();

    $args = [
        'posts_per_page' => 20,
        'paged' => 1
    ];

    echo '<div id="' . esc_attr($atts['id']) . '">';
    mre_afficher_resultats($args);

    // Vérifie s'il faut afficher le bouton
    $query = new WP_Query(array_merge($args, [
        'post_type' => 'evenements',
        'meta_key' => 'evenement_date_debut',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    ]));

    if ($query->max_num_pages > 1) {
        echo '<div class="mre-load-more-wrapper" style="text-align:center; margin-top: 2em;">';
        echo '<button id="mre-load-more" data-page="2">Charger plus</button>';
        echo '</div>';
    }

    echo '</div>';

    return ob_get_clean();
}


// Fonction de rendu de la grille (utilisée dans shortcode + AJAX)
function mre_afficher_resultats($args = []) {
    if (!is_array($args)) {
        $args = [];
    }

    $paged = isset($args['paged']) ? max(1, intval($args['paged'])) : 1;

    $defaults = [
        'post_type' => 'evenements',
        'posts_per_page' => 20,
        'paged' => $paged,
        'meta_key' => 'evenement_date_debut',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'meta_query' => [
            [
                'key' => 'evenement_date_debut',
                'value' => date('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE'
            ]
        ],
        'tax_query' => [],
        's' => ''
    ];

    // ⚠️ Use array_merge to prioritize $args over $defaults
    $query = new WP_Query(array_merge($defaults, $args));

    if ($query->have_posts()) {
        echo '<div class="mre-grille">';
        while ($query->have_posts()) {
            $query->the_post();
            $img = get_the_post_thumbnail(get_the_ID(), 'medium');
            $ville = get_field('evenement_ville');
            $date = get_field('evenement_date_debut');
            $cats = get_the_terms(get_the_ID(), 'evenements_category');
            echo '<a href="' . get_permalink() . '" class="mre-carte">';
            echo $img ? '<div class="mre-img">' . $img . '</div>' : '';
            if ($cats && !is_wp_error($cats)) {
                echo '<div class="mre-categories">';
                foreach ($cats as $cat) {
                    echo '<span class="mre-categorie">' . esc_html($cat->name) . '</span> ';
                }
                echo '</div>';
            }
            echo '<h3>' . get_the_title() . '</h3>';
            echo '<p class="mre-meta">' . esc_html($date) . ' - ' . esc_html($ville) . '</p>';
            echo '</a>';
        }
        echo '</div>';
    } else {
        echo '<p>Aucun événement trouvé.</p>';
    }

    wp_reset_postdata();
}


// Handler AJAX pour suggestions ville (version statique optimisée)
add_action('wp_ajax_mre_suggestions_ville', 'mre_suggestions_ville');
add_action('wp_ajax_nopriv_mre_suggestions_ville', 'mre_suggestions_ville');

function mre_suggestions_ville() {
    $term = sanitize_text_field($_GET['term'] ?? '');

    $villes = [
        'Altkirch', 'Ardennes', 'Aube', 'Autun', 'Auxerre', 'Avallon', 'Bar-le-Duc', 'Bar-sur-Aube',
        'Bas-Rhin', 'Beaune', 'Belfort', 'Besançon', 'Bourgogne-Franche-Comté', 'Chalon-sur-Saône',
        'Charleville-Mézières', 'Charolles', 'Château-Chinon', 'Chaumont', 'Châlons-en-Champagne',
        'Clamecy', 'Colmar', 'Commercy', 'Cosne-Cours-sur-Loire', 'Côte-d\'Or', 'Dijon', 'Dole',
        'Doubs', 'Épernay', 'Forbach-Boulay-Moselle', 'Grand Est', 'Haguenau-Wissembourg', 'Haut-Rhin',
        'Haute-Marne', 'Haute-Saône', 'Jura', 'Langres', 'Lons-le-Saunier', 'Louhans', 'Lunéville',
        'Lure', 'Mâcon', 'Marne', 'Meurthe-et-Moselle', 'Meuse', 'Metz', 'Montbard', 'Montbéliard',
        'Molsheim', 'Moselle', 'Mulhouse', 'Nancy', 'Nevers', 'Nièvre', 'Nogent-sur-Seine',
        'Pontarlier', 'Reims', 'Rethel', 'Ribeauvillé', 'Saint-Claude', 'Saint-Dizier',
        'Sainte-Menehould', 'Saône-et-Loire', 'Sarrebourg-Château-Salins', 'Saverne', 'Sedan',
        'Sélestat-Erstein', 'Sens', 'Strasbourg', 'Territoire de Belfort', 'Thann-Guebwiller',
        'Thionville', 'Toul', 'Troyes', 'Verdun', 'Vesoul', 'Vitry-le-François', 'Vouziers', 'Yonne'
    ];

    $suggestions = array_filter($villes, function($ville) use ($term) {
        return stripos($ville, $term) !== false;
    });

    wp_send_json(array_values($suggestions));
}

// Handler AJAX principal pour filtrage
add_action('wp_ajax_mre_filtrer_evenements', 'mre_filtrer_evenements');
add_action('wp_ajax_nopriv_mre_filtrer_evenements', 'mre_filtrer_evenements');

function mre_filtrer_evenements() {
    if (!empty($_POST['reset'])) {
        mre_afficher_resultats([
            'posts_per_page' => 20,
        ]);
        wp_die();
    }

    $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
$ville = isset($_POST['ville']) ? sanitize_text_field($_POST['ville']) : '';
$categorie = isset($_POST['categorie']) ? sanitize_text_field($_POST['categorie']) : '';
$date_debut = isset($_POST['date_debut']) ? sanitize_text_field($_POST['date_debut']) : '';
$date_fin = isset($_POST['date_fin']) ? sanitize_text_field($_POST['date_fin']) : '';
$titre = isset($_POST['recherche']) ? sanitize_text_field($_POST['recherche']) : '';


    // Préparez les données nécessaires
    $all_cats = get_terms([
        'taxonomy' => 'evenements_category',
        'hide_empty' => false
    ]);

    // Crée une map d'alias
    $cat_aliases = [
        'ciné' => 'cinéma',
        'cinema' => 'cinéma',
        'film' => 'cinéma',
        'musique' => 'musique',
        'concert' => 'musique',
        'théâtre' => 'théâtre',
        'expo' => 'exposition',
        'spectacle' => 'spectacle'
    ];

    $matched_cat = '';
    foreach ($cat_aliases as $alias => $real_name) {
        if (stripos($recherche_libre, $alias) !== false) {
            $matched_cat = $real_name;
            break;
        }
    }

    // Recherche approximative si rien trouvé
    if (!$matched_cat) {
        foreach ($all_cats as $term) {
            similar_text(strtolower($term->name), strtolower($recherche_libre), $percent);
            if ($percent > 60) {
                $matched_cat = $term->name;
                break;
            }
        }
    }

    foreach ($all_cats as $term) {
        if ($matched_cat && strtolower($term->name) === strtolower($matched_cat)) {
            $categorie = $term->name;
        }
    }

    $villes_connues = [
        'Strasbourg', 'Reims', 'Metz', 'Mulhouse', 'Nancy', 'Colmar', 'Troyes', 'Charleville-Mézières',
        'Châlons-en-Champagne', 'Thionville', 'Haguenau', 'Schiltigheim', 'Épinal', 'Vandœuvre-lès-Nancy',
        'Illkirch-Graffenstaden', 'Saint-Dizier', 'Saint-Louis', 'Épernay', 'Montigny-lès-Metz', 'Chaumont',
        'Forbach', 'Bar-le-Duc', 'Sélestat', 'Saverne', 'Wissembourg', 'Sarrebourg', 'Saint-Avold',
        'Verdun', 'Toul', 'Vitry-le-François', 'Rethel', 'Sedan', 'Commercy', 'Lunéville', 'Neufchâteau',
        'Sarre-Union', 'Bitche', 'Pont-à-Mousson', 'Longwy', 'Hayange', 'Dijon', 'Besançon', 'Belfort',
        'Chalon-sur-Saône', 'Auxerre', 'Mâcon', 'Nevers', 'Sens', 'Montbéliard', 'Dole', 'Le Creusot',
        'Beaune', 'Pontarlier', 'Montceau-les-Mines', 'Lons-le-Saunier', 'Vesoul', 'Chenôve', 'Audincourt',
        'Autun', 'Avallon', 'Gray', 'Lure', 'Champagnole', 'Saint-Claude', 'Clamecy', 'Cosne-Cours-sur-Loire',
        'Charolles', 'Louhans', 'Cluny', 'Paray-le-Monial', 'Tournus', 'Joigny', 'Tonnerre', 'Château-Chinon',
        'Decize', 'La Charité-sur-Loire', 'Semur-en-Auxois', 'Nuits-Saint-Georges', 'Is-sur-Tille',
        'Chevigny-Saint-Sauveur'
    ];

    foreach ($villes_connues as $v) {
        if (stripos($recherche_libre, $v) !== false) {
            $ville = $v;
            break;
        }
    }

    // Recherche floue si rien trouvé
    if (!$ville) {
        foreach ($villes_connues as $v) {
            similar_text(strtolower($v), strtolower($recherche_libre), $score);
            if ($score > 65) {
                $ville = $v;
                break;
            }
        }
    }

    // Gère "ce soir", "demain"
    $lower = strtolower($recherche_libre);
    if (strpos($lower, 'demain') !== false) {
        $date_debut = $date_fin = date('Y-m-d', strtotime('+1 day'));
    } elseif (strpos($lower, 'ce soir') !== false || strpos($lower, "aujourd'hui") !== false) {
        $date_debut = $date_fin = date('Y-m-d');
    }

    // Nettoyer les infos résiduelles
    // Si aucun champ explicite n’a été rempli, on tente d’extraire infos depuis la recherche libre
    if (empty($_POST['ville']) && $ville) {
        $_POST['ville'] = $ville;
    }

    if (empty($_POST['categorie']) && $categorie) {
        $_POST['categorie'] = $categorie;
    }

    if (empty($_POST['date_debut']) && $date_debut) {
        $_POST['date_debut'] = $date_debut;
    }

    if (empty($_POST['date_fin']) && $date_fin) {
        $_POST['date_fin'] = $date_fin;
    }

    // Nettoyer le reste pour la recherche libre (ex : le titre)
  if (empty($_POST['recherche'])) {
    $titre = trim(str_ireplace([$ville, $categorie, 'ce soir', 'demain', "aujourd'hui"], '', $recherche_libre));
}

    $meta_query = [
        [
            'key' => 'evenement_date_debut',
            'value' => date('Y-m-d'),
            'compare' => '>=',
            'type' => 'DATE'
        ]
    ];

    if ($ville) {
        $meta_query[] = [
            'key' => 'evenement_ville',
            'value' => $ville,
            'compare' => 'LIKE'
        ];
    }

    if ($date_debut && $date_fin) {
        $meta_query[] = [
            'key' => 'evenement_date_debut',
            'value' => $date_fin,
            'compare' => '<=',
            'type' => 'DATE'
        ];
        $meta_query[] = [
            'key' => 'evenement_date_fin',
            'value' => $date_debut,
            'compare' => '>=',
            'type' => 'DATE'
        ];
    }

    $tax_query = [];
    if (!empty($categorie)) {
        $tax_query[] = [
            'taxonomy' => 'evenements_category',
            'field' => 'name',
            'terms' => [$categorie],
        ];
    }

$args = [
    'post_type' => 'evenements',
    'posts_per_page' => 20,
    'paged' => $paged,
    'meta_query' => $meta_query,
    'tax_query' => $tax_query,
    's' => $titre,
    'meta_key' => 'evenement_date_debut',
    'orderby' => 'meta_value',
    'order' => 'ASC'
];

// Affichage des événements
mre_afficher_resultats($args);

// Vérifie s’il reste des pages
$query = new WP_Query($args);

if ($query->max_num_pages > $paged) {
    $next_page = $paged + 1;
    echo '<div class="mre-load-more-wrapper" style="text-align:center; margin-top: 2em;">';
    echo '<button id="mre-load-more" data-page="' . $next_page . '">Charger plus</button>';
    echo '</div>';
}



}