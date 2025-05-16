<?php
if (!defined('ABSPATH')) exit;

if (!get_option('nimbus_enable_accessibilite', false)) return;

// Menu Accessibilité
add_action('admin_menu', function() {
    add_submenu_page(
        'nimbus-dashboard',
        'Accessibilité',
        'Accessibilité',
        'manage_options',
        'nimbus-accessibilite',
        'nimbus_accessibilite_page'
    );
}, 30);

function nimbus_accessibilite_page() {
    ?>
    <div class="wrap nimbus-admin">
        <h1>Accessibilité</h1>
        <p>Nimbus vous aide à vérifier l’accessibilité de votre site, détecter les problèmes fréquents et propose des guides pour les corriger sans casser votre design Divi.</p>
        <ul>
            <li>Contraste des couleurs</li>
            <li>Présence des balises alt sur les images</li>
            <li>Structure des titres (H1, H2...)</li>
            <li>Navigation clavier & skip link</li>
            <li>Compatibilité lecteurs d’écran</li>
        </ul>
        <p><strong>À venir : checklist interactive, scan automatique, guides pas-à-pas.</strong></p>
    </div>
    <?php
}