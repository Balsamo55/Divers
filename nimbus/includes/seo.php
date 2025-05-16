<?php
if (!defined('ABSPATH')) exit;

if (!get_option('nimbus_enable_seo', false)) return;

// Menu SEO
add_action('admin_menu', function() {
    add_submenu_page(
        'nimbus-dashboard',
        'SEO',
        'SEO',
        'manage_options',
        'nimbus-seo',
        'nimbus_seo_page'
    );
}, 40);

function nimbus_seo_page() {
    ?>
    <div class="wrap nimbus-admin">
        <h1>SEO</h1>
        <p>Nimbus vérifie les balises title/meta, la structure des liens, la présence d’attributs alt, la configuration sitemap, robots.txt, canonical... et vous guide pour corriger chaque point.</p>
        <ul>
            <li>Balises title & meta description</li>
            <li>Liens internes et liens brisés</li>
            <li>Images sans alt</li>
            <li>Sitemap, robots.txt, canonical</li>
        </ul>
        <p><strong>À venir : rapport SEO détaillé, checklist actionnable, liens directs vers l’édition.</strong></p>
    </div>
    <?php
}