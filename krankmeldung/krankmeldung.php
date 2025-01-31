<?php
/*
Plugin Name: Child Sicknote Form
Description: Ein Plugin, um Krankmeldungen von Schülern zu verwalten.
Version: 202501281518
Author: sbstnmrld
*/

defined('ABSPATH') || exit;

// Includes
require_once plugin_dir_path(__FILE__) . 'includes/form-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';

// Shortcode-Registrierung
function sicknote_form_shortcode() {
    ob_start();
    sicknote_render_form();
    return ob_get_clean();
}
add_shortcode('child_sicknote_form', 'sicknote_form_shortcode');

// Plugin-Aktivierung: Datenbank erstellen
function sicknote_activate() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'sicknote_classes';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        class_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Option für Sekretariats-E-Mail erstellen
    if (!get_option('sicknote_secretary_email')) {
        add_option('sicknote_secretary_email', '');
    }
}
register_activation_hook(__FILE__, 'sicknote_activate');
