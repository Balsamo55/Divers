<?php
if (!defined('ABSPATH')) exit;

if (!get_option('nimbus_enable_historique', false)) return;

add_action('admin_menu', function() {
    add_submenu_page(
        'nimbus-dashboard',
        'Historique des audits',
        'Historique',
        'manage_options',
        'nimbus-historique',
        'nimbus_historique_page'
    );
}, 60);

function nimbus_historique_page() {
    $history = get_option('nimbus_audit_history', []);
    ?>
    <div class="wrap nimbus-admin">
        <h1>Historique des audits</h1>
        <?php if (!$history): ?>
            <p>Aucun audit enregistré.</p>
        <?php else: ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Performance</th>
                        <th>Accessibilité</th>
                        <th>Bonnes pratiques</th>
                        <th>SEO</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (array_reverse($history) as $h): ?>
                    <tr>
                        <td><?php echo esc_html($h['date']); ?></td>
                        <td><?php echo esc_html($h['scores']['Performance'] ?? '-'); ?></td>
                        <td><?php echo esc_html($h['scores']['Accessibilité'] ?? '-'); ?></td>
                        <td><?php echo esc_html($h['scores']['Bonnes pratiques'] ?? '-'); ?></td>
                        <td><?php echo esc_html($h['scores']['SEO'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <form method="post">
                <button type="submit" name="nimbus_export_csv" class="button">Exporter CSV</button>
            </form>
            <?php
            if (isset($_POST['nimbus_export_csv'])) {
                nimbus_export_csv($history);
            }
            ?>
        <?php endif; ?>
    </div>
    <?php
}

function nimbus_export_csv($history) {
    $filename = "nimbus-historique-".date("Y-m-d-H-i").".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date', 'Performance', 'Accessibilité', 'Bonnes pratiques', 'SEO']);
    foreach ($history as $h) {
        fputcsv($out, [
            $h['date'],
            $h['scores']['Performance'] ?? '',
            $h['scores']['Accessibilité'] ?? '',
            $h['scores']['Bonnes pratiques'] ?? '',
            $h['scores']['SEO'] ?? ''
        ]);
    }
    fclose($out);
    exit;
}