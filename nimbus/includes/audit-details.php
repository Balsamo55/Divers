<?php
if (!defined('ABSPATH')) exit;

if (!get_option('nimbus_enable_audit_details', false)) return;

// Ajoute un sous-menu pour le détail de l’audit
add_action('admin_menu', function() {
    add_submenu_page(
        'nimbus-dashboard',
        'Audit détaillé',
        'Audit détaillé',
        'manage_options',
        'nimbus-audit-details',
        'nimbus_audit_details_page'
    );
}, 15);

function nimbus_audit_details_page() {
    $history = get_option('nimbus_audit_history', []);
    $last_audit = $history ? end($history) : null;
    $details = [];

    // Si un audit existe, on affiche les détails
    if ($last_audit) {
        $api_key = get_option('nimbus_api_key', '');
        $site_url = home_url();
        $error = '';
        if (!$api_key) {
            $error = "Veuillez renseigner votre clé API dans Réglages Nimbus.";
        } else {
            $api = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=" . urlencode($site_url) . "&key=" . $api_key . "&strategy=mobile";
            $response = wp_remote_get($api);
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (isset($data['lighthouseResult']['audits'])) {
                    $audits = $data['lighthouseResult']['audits'];
                    foreach ($audits as $id => $a) {
                        if (isset($a['score']) && $a['score'] !== null && $a['score'] < 1) {
                            $details[$id] = $a;
                        }
                    }
                }
            }
        }
    }
    ?>
    <div class="wrap nimbus-admin">
        <h1>Audit détaillé PageSpeed</h1>
        <?php if (!$last_audit): ?>
            <p>Aucun audit trouvé. Lancez un audit dans l’onglet "Audit PageSpeed".</p>
        <?php else: ?>
            <p>Voici les points à améliorer selon Google PageSpeed (mobile) :</p>
            <?php if (!empty($details)): ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Problème</th>
                            <th>Explication</th>
                            <th>Score</th>
                            <th>Guide / Correctif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $id => $a): ?>
                            <tr>
                                <td><strong><?php echo esc_html($a['title']); ?></strong></td>
                                <td><?php echo esc_html(strip_tags($a['description'])); ?></td>
                                <td><?php echo isset($a['score']) ? round($a['score'] * 100) : '-'; ?>/100</td>
                                <td>
                                    <?php
                                    if (function_exists('nimbus_get_guide_link')) {
                                        echo nimbus_get_guide_link($id, $a['title']);
                                    } else {
                                        echo '<em>Bientôt : guide dédié.</em>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Félicitations ! Aucun problème critique détecté lors du dernier audit.</p>
            <?php endif; ?>
            <div class="nimbus-help" style="margin-top:2em;">
                <strong>Besoin d’un guide ? </strong>
                Les guides Nimbus seront bientôt intégrés pour chaque type de problème détecté.
            </div>
        <?php endif; ?>
    </div>
    <?php
}