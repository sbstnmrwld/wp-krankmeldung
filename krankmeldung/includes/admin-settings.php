<?php
defined('ABSPATH') || exit;

function sicknote_admin_menu() {
    add_menu_page('Krankmeldungen', 'Krankmeldungen', 'manage_options', 'sicknote-settings', 'sicknote_settings_page');
}
add_action('admin_menu', 'sicknote_admin_menu');

function sicknote_settings_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sicknote_classes';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['save_class'])) {
            // Speichere eine bestehende Klasse
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
            // Lösche eine bestehende Klasse
            $class_id = intval($_POST['class_id']);
            if (!empty($class_id)) {
                $wpdb->delete($table_name, ['id' => $class_id], ['%d']);
            }
        } elseif (isset($_POST['save_new_class'])) {
            // Speichere eine neue Klasse
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
            // Speichere die Sekretariats-E-Mail
            $secretary_email = sanitize_email($_POST['secretary_email']);
            update_option('sicknote_secretary_email', $secretary_email);
        }
    }

    // Aktuelle Klassen und Sekretariats-E-Mail laden
    $classes = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    $secretary_email = get_option('sicknote_secretary_email', '');
    ?>

    <div class="wrap">
        <h1>Einstellungen für Krankmeldungen</h1>
        <p>Verwenden Sie diesen Shortcode, um das Formular in eine Seite einzufügen:</p>
        <code>[child_sicknote_form]</code>

        <h2>Klassen</h2>
        <?php foreach ($classes as $class): ?>
            <form method="POST" class="class-row">
                <input type="hidden" name="class_id" value="<?php echo esc_attr($class['id']); ?>">
                <input type="text" name="class_name" value="<?php echo esc_attr($class['class_name']); ?>" placeholder="Klassenname">
                <input type="email" name="class_email" value="<?php echo esc_attr($class['email']); ?>" placeholder="E-Mail-Adresse">
                <button type="submit" name="save_class" class="icon-button save-button" title="Speichern">
                    💾
                </button>
                <button type="submit" name="delete_class" class="icon-button delete-button" title="Löschen">
                    🗑️
                </button>
            </form>
        <?php endforeach; ?>

        <h2>Neue Klasse hinzufügen</h2>
        <form method="POST" class="new-class-form">
            <input type="text" name="new_class_name" placeholder="Klassenname" required>
            <input type="email" name="new_class_email" placeholder="E-Mail-Adresse" required>
            <button type="submit" name="save_new_class" class="icon-button add-button" title="Neue Klasse hinzufügen">
                ➕
            </button>
        </form>

        <h2>Sekretariat</h2>
        <form method="POST" class="secretary-email-form">
            <input type="email" name="secretary_email" value="<?php echo esc_attr($secretary_email); ?>" required placeholder="E-Mail-Adresse des Sekretariats">
            <button type="submit" name="save_secretary_email" class="icon-button save-button" title="Speichern">
                💾
            </button>
        </form>
    </div>

    <style>
        .class-row, .new-class-form, .secretary-email-form {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .class-row input[type="text"],
        .class-row input[type="email"],
        .new-class-form input[type="text"],
        .new-class-form input[type="email"],
        .secretary-email-form input[type="email"] {
            margin-right: 10px;
            padding: 5px;
            width: 200px;
        }

        .icon-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 5px;
            margin-left: 5px;
        }

        .icon-button:hover {
            opacity: 0.8;
        }

        .save-button {
            color: green;
        }

        .delete-button {
            color: red;
        }

        .add-button {
            color: blue;
        }
    </style>
    <?php
}
