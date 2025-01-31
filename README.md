# Krankmeldungs-Formular für Schulen

## Beschreibung

Das **Krankmeldungs-Formular**-Plugin für WordPress ermöglicht es, Krankmeldungen digital an das Sekretariat und den Klassenlehrer zu senden. Eltern können über ein einfaches Formular eine Krankmeldung absenden, die dann per E-Mail an die zuständigen Stellen weitergeleitet wird.

## Funktionen

- 📩 **Formular für Krankmeldungen** direkt auf der Schulwebsite
- ✉️ **Automatische E-Mail-Benachrichtigung** an das Sekretariat und den Klassenlehrer
- 🔒 **Sicherheitsschutz** gegen Spam und unerwünschte Einsendungen
- 🖥️ **Einfache Verwaltung** der Einstellungen im WordPress-Adminbereich

## Installation

1. Lade den Plugin-Ordner `krankmeldung` in das Verzeichnis `/wp-content/plugins/` deiner WordPress-Installation hoch.
2. Aktiviere das Plugin über die **Plugins**-Seite im WordPress-Adminbereich.
3. Konfiguriere das Plugin über das Menü **Krankmeldungen** in der WordPress-Administration.

## Verwendung

1. **Shortcode einfügen**: Das Formular kann über den Shortcode `[krankmeldung_form]` in einer Seite oder einem Beitrag eingefügt werden.
2. **Krankmeldungen ausfüllen**: Eltern tragen die notwendigen Daten ein und senden das Formular ab.
3. **E-Mail-Benachrichtigungen**: Die Krankmeldung wird automatisch an das Sekretariat und den Klassenlehrer gesendet.

## Entwicklung

### Hooks & Actions

- **`admin_menu`** – Erstellt das Menü für die Plugin-Einstellungen im Admin-Panel.
- **`register_activation_hook(__FILE__, 'km_activate')`** – Setzt Standardoptionen bei der Aktivierung.
- **`register_deactivation_hook(__FILE__, 'km_deactivate')`** – Entfernt Optionen bei der Deaktivierung.
- **`wp_enqueue_scripts`** – Lädt CSS- und JavaScript-Dateien für das Frontend-Formular.
- **`add_shortcode('krankmeldung_form', 'krankmeldung_form_shortcode')`** – Registriert den Shortcode für das Formular.

### Sicherheit

- **`sanitize_text_field()`** – Entfernt unsichere HTML-Elemente aus Eingaben.
- **`wp_nonce_field()`** – Schützt das Formular vor CSRF-Angriffen.
- **`esc_html()`** – Verhindert Cross-Site Scripting (XSS) in der Ausgabe.

## Lizenz

Dieses Plugin ist unter der **GPL-2.0** oder höher lizenziert.
