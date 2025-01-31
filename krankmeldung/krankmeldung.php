<?php
/*
Plugin Name: Child krankmeldung Form
Description: Ein Plugin, um Krankmeldungen von Schülern zu verwalten.
Version: 202501281518
Author: sbstnmrld
*/

defined('ABSPATH') || exit;

// Includes
require_once plugin_dir_path(__FILE__) . 'includes/form-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';

// Shortcode-Registrierung
function krankmeldung_form_shortcode() {
    ob_start();
    krankmeldung_render_form();
    return ob_get_clean();
}
add_shortcode('krankmeldung_form', 'krankmeldung_form_shortcode');

// Plugin-Aktivierung: Datenbank erstellen
function krankmeldung_activate() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'krankmeldung_classes';
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
    if (!get_option('krankmeldung_secretary_email')) {
        add_option('krankmeldung_secretary_email', '');
    }
}
register_activation_hook(__FILE__, 'krankmeldung_activate');
