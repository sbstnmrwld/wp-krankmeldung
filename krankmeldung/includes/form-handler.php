<?php
/*
Version: 202501281528
Author: sbstnmrld
*/

defined('ABSPATH') || exit;

if (!function_exists('sicknote_render_form')) {
    function sicknote_render_form() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sicknote_classes';
        $classes = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sicknote_submit'])) {
            $result = sicknote_process_form();
            if ($result === true) {
                echo '<p class="success-message">Die Krankmeldung wurde erfolgreich gesendet!</p>';
            } elseif ($result === false) {
                echo '<p class="error-message">Es gab ein Problem beim Versenden der E-Mail. Bitte versuchen Sie es später erneut.</p>';
            } else {
                echo '<p class="error-message">' . esc_html($result) . '</p>';
            }
        }

        ?>

        <form id="sicknote-form" method="POST">
            <label for="child_name">Vollständiger Name des Kindes:</label>
            <input type="text" name="child_name" class="widefat" required>

            <label for="class">Klasse:</label>
            <select name="class" class="widefat" required>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo esc_attr($class['class_name']); ?>"><?php echo esc_html($class['class_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="start_date">Beginn Krankmeldung:</label>
            <input type="date" name="start_date" class="widefat" required>

            <label for="end_date">Ende Krankmeldung:</label>
            <input type="date" name="end_date" class="widefat" required>

            <label for="reason">Grund der Krankmeldung:</label>
            <textarea name="reason" class="widefat" required></textarea>

            <label for="sender_name">Ihr Name (Absender):</label>
            <input type="text" name="sender_name" class="widefat" required>

            <label for="sender_email">Ihre E-Mail-Adresse:</label>
            <input type="email" name="sender_email" class="widefat" required>

            <!-- Honeypot-Feld -->
            <label class="honeypot-label" style="display: none;">Honeypot:</label>
            <input type="text" name="honeypot" class="honeypot-field" style="display: none;">

            <button type="submit" name="sicknote_submit" class="button">Absenden</button>
        </form>

        <style>
            form#sicknote-form {
                display: flex;
                flex-direction: column;
                gap: 8px; /* Abstand zwischen Feldern */
            }

            form#sicknote-form label {
                margin-bottom: 4px; /* Abstand zwischen Feldtitel und Eingabefeld */
                font-weight: bold;
            }

            form#sicknote-form input,
            form#sicknote-form select,
            form#sicknote-form textarea {
                margin-bottom: 8px; /* Abstand zwischen den Eingabefeldern */
                padding: 8px;
                font-size: 16px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            form#sicknote-form button {
                font-size: 16px;
                border-radius: 4px;
                padding: 10px 15px;
                cursor: pointer;
            }

            .success-message {
                color: #155724;
                background-color: #d4edda;
                border: 1px solid #c3e6cb;
                padding: 10px;
                border-radius: 4px;
                margin-bottom: 15px;
            }

            .error-message {
                color: #721c24;
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                padding: 10px;
                border-radius: 4px;
                margin-bottom: 15px;
            }

            .honeypot-label,
            .honeypot-field {
                display: none;
            }
        </style>
        <?php
    }
}

if (!function_exists('sicknote_process_form')) {
    function sicknote_process_form() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sicknote_classes';

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
        $secretary_email = get_option('sicknote_secretary_email', '');

        $class_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE class_name = %s", $class), ARRAY_A);
        if (!$class_data) {
            return 'Die ausgewählte Klasse konnte nicht gefunden werden.';
        }

        $class_email = $class_data['email'];

        if (empty($class_email) || empty($secretary_email)) {
            return 'E-Mail-Adressen der Klasse oder des Sekretariats fehlen.';
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
