<?php
defined('ABSPATH') || exit;

function krankmeldung_admin_menu() {
    add_menu_page(
        'Krankmeldungen',
        'Krankmeldungen',
        'manage_options',
        'krankmeldung-settings',
        'krankmeldung_settings_page',
        'dashicons-clipboard',
        99
    );
}
add_action('admin_menu', 'krankmeldung_admin_menu');

function krankmeldung_enqueue_admin_styles($hook) {
    if ($hook !== 'toplevel_page_krankmeldung-settings') {
        return;
    }
    wp_enqueue_style('krankmeldung-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css', [], '1.0.2');
}
add_action('admin_enqueue_scripts', 'krankmeldung_enqueue_admin_styles');

function krankmeldung_settings_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'krankmeldung_classes';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['save_class'])) {
            $class_id = intval($_POST['class_id']);
            $class_name = sanitize_text_field($_POST['class_name']);
            $class_email = sanitize_email($_POST['class_email']);

            if (!empty($class_id) && !empty($class_name) && !empty($class_email)) {
                $wpdb->update(
                    $table_name,
                    ['class_name' => $class_name, 'email' => $class_email],
                    ['id' => $class_id],
                    ['%s', '%s'],
                    ['%d']
                );
            }
        } elseif (isset($_POST['delete_class'])) {
            $class_id = intval($_POST['class_id']);
            if (!empty($class_id)) {
                $wpdb->delete($table_name, ['id' => $class_id], ['%d']);
            }
        } elseif (isset($_POST['save_new_class'])) {
            $new_class_name = sanitize_text_field($_POST['new_class_name']);
            $new_class_email = sanitize_email($_POST['new_class_email']);

            if (!empty($new_class_name) && !empty($new_class_email)) {
                $wpdb->insert(
                    $table_name,
                    ['class_name' => $new_class_name, 'email' => $new_class_email],
                    ['%s', '%s']
                );
            }
        } elseif (isset($_POST['save_secretary_email'])) {
            $secretary_email = sanitize_email($_POST['secretary_email']);
            update_option('krankmeldung_secretary_email', $secretary_email);
        }
    }

    $classes = $wpdb->get_results("SELECT * FROM $table_name ORDER BY class_name ASC", ARRAY_A);
    $secretary_email = get_option('krankmeldung_secretary_email', '');
    ?>

    <div class="wrap">
        <h1>Einstellungen für Krankmeldungen</h1>

        <h2>Neue Klasse hinzufügen</h2>
        <form method="POST" class="new-class-form">
            <table class="form-table">
                <tr>
                    <td>
                        <input type="text" name="new_class_name" placeholder="Klassenname" required class="regular-text">
                    </td>
                    <td>
                        <input type="email" name="new_class_email" placeholder="E-Mail-Adresse" required class="regular-text">
                    </td>
                    <td>
                        <button type="submit" name="save_new_class" class="button button-primary">Hinzufügen</button>
                    </td>
                </tr>
            </table>
        </form>

        <h2>Bestehende Klassen</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Klassenname</th>
                    <th>E-Mail-Adresse</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                    <tr>
                        <form method="POST">
                            <input type="hidden" name="class_id" value="<?php echo esc_attr($class['id']); ?>">
                            <td>
                                <input type="text" name="class_name" value="<?php echo esc_attr($class['class_name']); ?>" class="regular-text">
                            </td>
                            <td>
                                <input type="email" name="class_email" value="<?php echo esc_attr($class['email']); ?>" class="regular-text">
                            </td>
                            <td>
                                <button type="submit" name="save_class" class="button button-primary">Speichern</button>
                                <button type="submit" name="delete_class" class="button button-secondary delete-button">Löschen</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Sekretariat</h2>
        <form method="POST" class="secretary-email-form">
            <input type="email" name="secretary_email" value="<?php echo esc_attr($secretary_email); ?>" class="regular-text" required placeholder="E-Mail-Adresse des Sekretariats">
            <button type="submit" name="save_secretary_email" class="button button-primary">Speichern</button>
        </form>

        <hr>

        <h2>Shortcode</h2>
        <p>Verwenden Sie den folgenden Shortcode, um das Krankmeldungsformular in eine Seite einzufügen:</p>
        <code>[krankmeldung_form]</code>
    </div>
<?php
}
