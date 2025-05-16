<?php
if (!defined('ABSPATH')) exit;

// N'affiche rien si le module n'est pas activé
if (!get_option('nimbus_enable_audit', false)) return;

// Menu & page Audit PageSpeed
add_action('admin_menu', function() {
    add_menu_page(
        'Nimbus',
        'Nimbus',
        'manage_options',
        'nimbus-dashboard',
        'nimbus_dashboard_page',
        'dashicons-cloud',
        55
    );
    add_submenu_page(
        'nimbus-dashboard',
        'Audit Google PageSpeed',
        'Audit PageSpeed',
        'manage_options',
        'nimbus-audit',
        'nimbus_audit_page'
    );
}, 1);

function nimbus_dashboard_page() {
    ?>
    <div class="wrap nimbus-admin">
        <h1>☁️ Bienvenue sur <span style="color:#4893f5">Nimbus</span></h1>
        <p class="nimbus-desc">Nimbus vous aide à comprendre, améliorer et monitorer la performance, l’accessibilité et le SEO de votre site WordPress.</p>
        <div class="nimbus-sections">
            <div class="nimbus-section">
                <h2>Derniers scores PageSpeed</h2>
                <div class="nimbus-score-placeholder">En attente d’audit…</div>
            </div>
            <div class="nimbus-section">
                <h2>Modules inutilisés détectés</h2>
                <div class="nimbus-score-placeholder">Voir <a href="?page=nimbus-modules">Modules inutilisés</a></div>
            </div>
            <div class="nimbus-section">
                <h2>Accessibilité & SEO</h2>
                <div class="nimbus-score-placeholder">Voir <a href="?page=nimbus-accessibilite">Accessibilité</a> / <a href="?page=nimbus-seo">SEO</a></div>
            </div>
        </div>
        <hr/>
        <h3>Pourquoi Nimbus ?</h3>
        <p>
            Nimbus analyse votre site, détecte les optimisations possibles, et vous guide pas à pas grâce à une interface claire et pédagogique.<br>
            <strong>Gagnez en performance, en accessibilité et en visibilité… sereinement !</strong>
        </p>
        <p style="font-size:0.93em;color:#888;">Un plugin créé par <a href="https://lesideesfixes.fr" target="_blank" style="color:#4893f5;">Les Idées Fixes</a></p>
    </div>
    <?php
}

function nimbus_audit_page() {
    $api_key = get_option('nimbus_api_key', '');
    $site_url = home_url();
    $scores = false;
    $error = '';

    // Audit lancé à la demande
    if (isset($_POST['nimbus_audit_launch'])) {
        if (!$api_key) {
            $error = "Veuillez renseigner votre clé API dans Réglages Nimbus.";
        } else {
            // Appel API Google PageSpeed Insights
            $api = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=" . urlencode($site_url) . "&key=" . $api_key . "&strategy=mobile";
            $response = wp_remote_get($api);
            if (is_wp_error($response)) {
                $error = "Erreur lors de la connexion à l’API : " . $response->get_error_message();
            } else {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (isset($data['lighthouseResult'])) {
                    $lr = $data['lighthouseResult'];
                    $scores = [
                        'Performance' => intval($lr['categories']['performance']['score'] * 100),
                        'Accessibilité' => intval($lr['categories']['accessibility']['score'] * 100),
                        'Bonnes pratiques' => intval($lr['categories']['best-practices']['score'] * 100),
                        'SEO' => intval($lr['categories']['seo']['score'] * 100),
                    ];
                    // Historique (optionnel)
                    $history = get_option('nimbus_audit_history', []);
                    $history[] = [
                        'date' => current_time('mysql'),
                        'scores' => $scores,
                    ];
                    update_option('nimbus_audit_history', array_slice($history, -20)); // Garde 20 audits max
                } else {
                    $error = "Réponse API inattendue. Vérifiez votre clé et quota Google.";
                }
            }
        }
    } else {
        // Dernier audit en BDD
        $history = get_option('nimbus_audit_history', []);
        if ($history && isset($history[count($history)-1]['scores'])) {
            $scores = $history[count($history)-1]['scores'];
        }
    }
    ?>
    <div class="wrap nimbus-admin">
        <h1>Audit Google PageSpeed</h1>
        <p>Lancez un audit complet de votre site. Nimbus affiche vos scores, détaille chaque point d’amélioration, et vous propose des correctifs clairs et guidés.</p>
        <form method="post" class="nimbus-audit-controls">
            <input type="submit" name="nimbus_audit_launch" class="button button-primary" value="Lancer un audit maintenant">
        </form>
        <?php if ($error): ?>
            <div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div>
        <?php endif; ?>
        <?php if ($scores): ?>
            <div class="nimbus-audit-results">
                <h2>Scores PageSpeed (Mobile)</h2>
                <ul class="nimbus-scores">
                    <?php foreach ($scores as $label => $score): ?>
                        <li>
                            <span class="nimbus-score-label"><?php echo esc_html($label); ?></span>
                            <span class="nimbus-score-value nimbus-score-<?php echo nimbus_score_color($score); ?>"><?php echo $score; ?>/100</span>
                            <span class="nimbus-score-help"><?php echo nimbus_score_help($label); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <hr>
            <h3>Explications pédagogiques</h3>
            <ul>
                <li><strong>Performance</strong> : rapidité d’affichage, gestion des ressources, images, etc.</li>
                <li><strong>Accessibilité</strong> : contraste des couleurs, balises alt, navigation clavier…</li>
                <li><strong>Bonnes pratiques</strong> : sécurité, code propre, ressources externes fiables…</li>
                <li><strong>SEO</strong> : balises meta, structure, indexabilité…</li>
            </ul>
        <?php endif; ?>
        <hr>
        <h3>Historique des audits</h3>
        <table class="widefat">
            <thead><tr><th>Date</th><th>Performance</th><th>Accessibilité</th><th>Bonnes pratiques</th><th>SEO</th></tr></thead>
            <tbody>
            <?php
            $history = get_option('nimbus_audit_history', []);
            if ($history) {
                foreach (array_reverse($history) as $h) {
                    echo '<tr><td>' . esc_html($h['date']) . '</td>';
                    foreach (['Performance','Accessibilité','Bonnes pratiques','SEO'] as $cat) {
                        $sc = isset($h['scores'][$cat]) ? $h['scores'][$cat] : '-';
                        echo '<td>' . esc_html($sc) . '</td>';
                    }
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="5">Aucun audit réalisé.</td></tr>';
            }
            ?>
            </tbody>
        </table>
        <hr>
        <div class="nimbus-help">
            <h3>Comment améliorer ces scores ?</h3>
            <p>
                Nimbus vous proposera pour chaque point faible :  
                — Un guide pédagogique adapté à votre configuration (Divi, WP Rocket, Imagify…)  
                — Des correctifs actionnables ou guidés, sans effet de bord  
                — Lien direct vers la documentation ou l’élément concerné
            </p>
        </div>
    </div>
    <?php
}