<?php
if (!defined('ABSPATH')) exit;

if (!get_option('nimbus_enable_modules', false)) return;

// Menu Modules inutilisés
add_action('admin_menu', function() {
    add_submenu_page(
        'nimbus-dashboard',
        'Modules inutilisés',
        'Modules inutilisés',
        'manage_options',
        'nimbus-modules',
        'nimbus_modules_page'
    );
}, 20);

function nimbus_modules_page() {
    $divi_modules = function_exists('et_builder_get_modules') ? et_builder_get_modules() : [];
    $diviflash_modules = function_exists('diviflash_get_modules') ? diviflash_get_modules() : [];

    $used_modules = [];
    $args = ['post_type' => ['page', 'post'], 'posts_per_page' => -1];
    $query = new WP_Query($args);
    while ($query->have_posts()) {
        $query->the_post();
        $content = get_the_content();
        foreach ($divi_modules as $module) {
            if (strpos($content, '[et_pb_' . $module['slug']) !== false) {
                $used_modules[$module['slug']] = true;
            }
        }
        foreach ($diviflash_modules as $module) {
            if (strpos($content, '[diviflash_' . $module['slug']) !== false) {
                $used_modules['diviflash_' . $module['slug']] = true;
            }
        }
    }
    wp_reset_postdata();
    $unused_divi = array_filter($divi_modules, fn($m) => empty($used_modules[$m['slug']]));
    $unused_df = array_filter($diviflash_modules, fn($m) => empty($used_modules['diviflash_' . $m['slug']]));

    ?>
    <div class="wrap nimbus-admin">
        <h1>Modules inutilisés (Divi & DiviFlash)</h1>
        <p>Ces modules ne sont jamais utilisés sur vos pages : vous pouvez les désactiver pour alléger Divi et accélérer l’admin.</p>
        <div class="nimbus-module-columns">
            <div>
                <h2>Divi</h2>
                <?php if ($unused_divi): ?>
                    <ul>
                        <?php foreach ($unused_divi as $m): ?>
                        <li><strong><?php echo esc_html($m['name'] ?? $m['slug']); ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="nimbus-note">Désactivez ces modules dans les options Divi pour booster l’éditeur.</p>
                <?php else: ?>
                    <p>Aucun module inutilisé détecté (ou détection incomplète).</p>
                <?php endif; ?>
            </div>
            <div>
                <h2>DiviFlash</h2>
                <?php if ($unused_df): ?>
                    <ul>
                        <?php foreach ($unused_df as $m): ?>
                        <li><strong><?php echo esc_html($m['name'] ?? $m['slug']); ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="nimbus-note">Désactivez ces modules dans DiviFlash si possible.</p>
                <?php else: ?>
                    <p>Aucun module inutilisé détecté (ou détection incomplète).</p>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <div class="nimbus-help">
            <h3>Pourquoi désactiver les modules inutilisés ?</h3>
            <p>Moins de modules = un builder Divi plus rapide, moins de scripts chargés, une interface plus claire pour vos contributeurs.</p>
        </div>
    </div>
    <?php
}