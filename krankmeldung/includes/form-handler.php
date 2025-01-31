<?php

defined('ABSPATH') || exit;

if (!function_exists('krankmeldung_enqueue_styles')) {
    function krankmeldung_enqueue_styles() {
        $css_file = plugin_dir_path(__FILE__) . '../assets/css/form-style.css';
        $css_version = file_exists($css_file) ? filemtime($css_file) : '1.0.0';

        wp_enqueue_style(
            'krankmeldung-form-style',
            plugin_dir_url(__FILE__) . '../assets/css/form-style.css',
            [],
            $css_version
        );
    }
    add_action('wp_enqueue_scripts', 'krankmeldung_enqueue_styles');
}

if (!function_exists('krankmeldung_render_form')) {
    function krankmeldung_render_form() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'krankmeldung_classes';
        $classes = $wpdb->get_results("SELECT * FROM $table_name ORDER BY class_name ASC", ARRAY_A);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['krankmeldung_submit'])) {
            $result = krankmeldung_process_form();
            if ($result === true) {
                echo '<p class="success-message">Die Krankmeldung wurde erfolgreich gesendet!</p>';
            } elseif ($result === false) {
                echo '<p class="error-message">Es gab ein Problem beim Versenden der E-Mail. Bitte versuchen Sie es später erneut.</p>';
            } else {
                echo '<p class="error-message">' . esc_html($result) . '</p>';
            }
        }

        $current_date = date('Y-m-d'); // Aktuelles Datum

        ?>
        <form id="krankmeldung-form" method="POST">
            <label for="child_name">Vollständiger Name des Kindes:</label>
            <input type="text" name="child_name" class="widefat" required>

            <label for="class">Klasse:</label>
            <select name="class" class="widefat" required>
                <option value="" selected disabled>Bitte Klasse wählen</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo esc_attr($class['class_name']); ?>"><?php echo esc_html($class['class_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="start_date">Beginn Krankmeldung:</label>
            <input type="date" name="start_date" class="widefat" required value="<?php echo esc_attr($current_date); ?>">

            <label for="end_date">Ende Krankmeldung:</label>
            <input type="date" name="end_date" class="widefat" required value="<?php echo esc_attr($current_date); ?>">

            <label for="reason">Grund der Krankmeldung:</label>
            <textarea name="reason" class="widefat" required></textarea>

            <label for="sender_name">Ihr Name (Absender):</label>
            <input type="text" name="sender_name" class="widefat" required>

            <label for="sender_email">Ihre E-Mail-Adresse:</label>
            <input type="email" name="sender_email" class="widefat" required>

            <!-- Honeypot-Feld -->
            <label class="honeypot-label" style="display: none;">Honeypot:</label>
            <input type="text" name="honeypot" class="honeypot-field" style="display: none;">

            <button type="submit" name="krankmeldung_submit" class="button">Absenden</button>
        </form>
        <?php
    }
}

if (!function_exists('krankmeldung_process_form')) {
    function krankmeldung_process_form() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'krankmeldung_classes';

        if (!empty($_POST['honeypot'])) {
            return 'Spam erkannt. Anfrage abgelehnt.';
        }

        $child_name = sanitize_text_field($_POST['child_name']);
        $class = sanitize_text_field($_POST['class']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        $reason = sanitize_textarea_field($_POST['reason']);
        $sender_name = sanitize_text_field($_POST['sender_name']);
        $sender_email = sanitize_email($_POST['sender_email']);
        $secretary_email = get_option('krankmeldung_secretary_email', '');

        $class_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE class_name = %s", $class), ARRAY_A);
        if (!$class_data) {
            return 'Die ausgewählte Klasse konnte nicht gefunden werden.';
        }

        $class_email = $class_data['email'];

        if (empty($secretary_email)) {
            return 'E-Mail-Adresse des Sekretariats fehlt.';
        }

        if (empty($class_email)) {
            return 'E-Mail-Adresse der Klasse fehlt.';
        }

        $subject = "Krankmeldung: $child_name ($class)";
        $message = "
        <html>
        <head>
            <title>Krankmeldung</title>
        </head>
        <body>
            <table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
                <tr>
                    <th style='background-color: #f2f2f2;'>Feld</th>
                    <th style='background-color: #f2f2f2;'>Inhalt</th>
                </tr>
                <tr>
                    <td>Vollständiger Name des Kindes</td>
                    <td>$child_name</td>
                </tr>
                <tr>
                    <td>Klasse</td>
                    <td>$class</td>
                </tr>
                <tr>
                    <td>Beginn der Krankmeldung</td>
                    <td>$start_date</td>
                </tr>
                <tr>
                    <td>Ende der Krankmeldung</td>
                    <td>$end_date</td>
                </tr>
                <tr>
                    <td>Grund</td>
                    <td>$reason</td>
                </tr>
                <tr>
                    <td>Absender</td>
                    <td>$sender_name</td>
                </tr>
                <tr>
                    <td>E-Mail des Absenders</td>
                    <td>$sender_email</td>
                </tr>
            </table>
        </body>
        </html>
        ";
        $headers = [
            'From: ' . $sender_name . ' <' . $sender_email . '>',
            'CC: ' . $class_email,
            'Content-Type: text/html; charset=UTF-8',
        ];

        return wp_mail($secretary_email, $subject, $message, $headers) ? true : false;
    }
}
