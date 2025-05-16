<?php
if (!defined('ABSPATH')) exit;

if (!get_option('nimbus_enable_correctifs', false)) return;

// Fonction globale pour générer le lien vers le bon guide Nimbus selon l’audit technique
function nimbus_get_guide_link($audit_id, $title = '') {
    $guides = [
        'uses-long-cache-ttl' => [
            'label' => 'Mettre en cache vos ressources statiques',
            'url'   => 'https://lesideesfixes.fr/nimbus/guides/cache-static-files'
        ],
        'uses-optimized-images' => [
            'label' => 'Optimiser vos images (Imagify, WP Rocket)',
            'url'   => 'https://lesideesfixes.fr/nimbus/guides/optimiser-images'
        ],
        'render-blocking-resources' => [
            'label' => 'Éliminer les ressources bloquantes (Divi, WP Rocket)',
            'url'   => 'https://lesideesfixes.fr/nimbus/guides/render-blocking'
        ],
        'uses-text-compression' => [
            'label' => 'Activer la compression GZIP/Brotli',
            'url'   => 'https://lesideesfixes.fr/nimbus/guides/compression'
        ],
        'uses-rel-preload' => [
            'label' => 'Précharger les ressources critiques',
            'url'   => 'https://lesideesfixes.fr/nimbus/guides/preload'
        ],
        // ...ajoute d’autres mappings ici
    ];
    if (isset($guides[$audit_id])) {
        return '<a href="' . esc_url($guides[$audit_id]['url']) . '" target="_blank" class="button">' . esc_html($guides[$audit_id]['label']) . '</a>';
    }
    return '<a href="https://www.google.com/search?q=' . urlencode('Corriger ' . $title . ' PageSpeed WordPress') . '" target="_blank">Voir sur Google</a>';
}