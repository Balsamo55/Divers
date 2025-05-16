<?php
if (!defined('ABSPATH')) exit;

if (!get_option('nimbus_enable_notifications', false)) return;

// Email admin si score faible après audit
add_action('admin_init', function() {
    $history = get_option('nimbus_audit_history', []);
    if ($history) {
        $last = end($history);
        $score = $last['scores']['Performance'] ?? 100;
        if ($score <= 60 && empty($last['notified'])) {
            // Envoie email
            $to = get_option('admin_email');
            $subject = "Nimbus : Score PageSpeed faible détecté";
            $message = "Bonjour,\n\nLe score Performance PageSpeed de votre site est descendu à $score/100.\nConnectez-vous à l’admin Nimbus pour voir et corriger les points faibles.";
            wp_mail($to, $subject, $message);
            // Marquer comme notifié
            $last['notified'] = true;
            $history[count($history)-1] = $last;
            update_option('nimbus_audit_history', $history);
        }
    }
});