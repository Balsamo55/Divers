<?php
if (!defined('ABSPATH')) exit;

// Menu Réglages
add_action('admin_menu', function() {
    add_submenu_page(
        'nimbus-dashboard',
        'Réglages',
        'Réglages',
        'manage_options',
        'nimbus-settings',
        'nimbus_settings_page'
    );
}, 50);

function nimbus_settings_page() {
    // Liste des modules Nimbus
    $modules = [
        'audit'         => 'Audit PageSpeed',
        'audit_details' => 'Audit détaillé',
        'correctifs'    => 'Correctifs & Guides',
        'modules'       => 'Modules inutilisés',
        'accessibilite' => 'Accessibilité',
        'seo'           => 'SEO',
        'historique'    => 'Historique',
        'notifications' => 'Notifications',
    ];

    // Enregistrement des options
    if (isset($_POST['nimbus_modules'])) {
        foreach ($modules as $key => $label) {
            update_option('nimbus_enable_' . $key, !empty($_POST['nimbus_modules'][$key]));
        }
        if (isset($_POST['nimbus_api_key'])) {
            update_option('nimbus_api_key', sanitize_text_field($_POST['nimbus_api_key']));
        }
        echo '<div class="updated"><p>Réglages enregistrés.</p></div>';
    }

    // Lecture options (tout désactivé par défaut)
    $api_key = get_option('nimbus_api_key', '');
    ?>
    <div class="wrap nimbus-admin">
        <h1>Réglages Nimbus</h1>
        <form method="post">
            <h2>Modules actifs</h2>
            <p>Cochez les modules à activer :</p>
            <ul style="columns:2;max-width:500px;">
                <?php foreach ($modules as $key => $label): ?>
                    <li>
                        <label>
                            <input type="checkbox" name="nimbus_modules[<?php echo $key; ?>]" value="1"
                            <?php checked(get_option('nimbus_enable_' . $key, false)); ?> />
                            <?php echo esc_html($label); ?>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
            <h2>Clé API PageSpeed</h2>
            <input type="text" name="nimbus_api_key" id="nimbus_api_key" value="<?php echo esc_attr($api_key); ?>" style="width:300px;">
            <button class="button button-primary" type="submit">Enregistrer</button>
        </form>
        <p><a href="https://developers.google.com/speed/docs/insights/v5/get-started" target="_blank">Obtenir une clé API</a></p>
    </div>
    <?php
}