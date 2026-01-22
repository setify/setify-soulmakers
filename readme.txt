=== Soulmakers ===
Contributors: setify
Tags: soulmakers, custom-functionality
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Soulmakers WordPress Plugin - Erweiterte Funktionalitäten für die Soulmakers Website.

== Description ==

Dieses Plugin stellt erweiterte Funktionalitäten für die Soulmakers Website bereit.

= Features =

* Modulare Plugin-Architektur
* Separate CSS/JS für Frontend und Backend
* SweetAlert2 Integration für Dialoge
* Select2 Integration für erweiterte Dropdowns
* AJAX-Handler vorbereitet
* Mehrsprachig vorbereitet (i18n-ready)

= Kompatibilität =

Getestet und kompatibel mit:

* WordPress 6.0+
* PHP 8.0+
* Elementor & Elementor Pro
* Fluent Forms & Fluent Forms Pro
* Advanced Custom Fields Pro
* Frontend Admin Pro für ACF
* Admin Columns Pro
* Rank Math SEO Pro
* LiteSpeed Cache
* Weitere installierte Plugins (siehe Plugin-Liste)

= Ordnerstruktur =

```
soulmakers/
├── assets/
│   ├── css/
│   │   ├── admin.css          # Backend-Styles
│   │   └── frontend.css       # Frontend-Styles
│   ├── js/
│   │   ├── admin.js           # Backend-JavaScript
│   │   └── frontend.js        # Frontend-JavaScript
│   ├── images/                # Plugin-Bilder
│   └── vendor/
│       ├── sweetalert2/       # SweetAlert2 Library (v11.14.0)
│       └── select2/           # Select2 Library (v4.1.0)
├── includes/
│   ├── class-soulmakers.php           # Hauptklasse
│   ├── class-soulmakers-admin.php     # Admin-Funktionalität
│   └── class-soulmakers-frontend.php  # Frontend-Funktionalität
├── languages/                 # Übersetzungsdateien
├── templates/                 # Template-Dateien
├── readme.txt                 # Diese Datei
└── soulmakers.php             # Plugin-Hauptdatei
```

= Verfügbare Konstanten =

* `SOULMAKERS_VERSION` - Aktuelle Plugin-Version
* `SOULMAKERS_PLUGIN_DIR` - Absoluter Pfad zum Plugin-Verzeichnis
* `SOULMAKERS_PLUGIN_URL` - URL zum Plugin-Verzeichnis
* `SOULMAKERS_PLUGIN_BASENAME` - Plugin-Basename
* `SOULMAKERS_ASSETS_URL` - URL zum Assets-Verzeichnis

= Verfügbare Hooks =

**Actions:**
* `soulmakers_init` - Wird bei Plugin-Initialisierung ausgelöst

= JavaScript-Objekte =

**Frontend:**
* `window.SoulmakersFrontend` - Frontend-Modul mit Helper-Funktionen
* `soulmakersFrontend.ajaxUrl` - AJAX-URL
* `soulmakersFrontend.nonce` - Sicherheits-Nonce

**Admin:**
* `window.SoulmakersAdmin` - Admin-Modul mit Helper-Funktionen
* `soulmakersAdmin.ajaxUrl` - AJAX-URL
* `soulmakersAdmin.nonce` - Sicherheits-Nonce

= CSS-Variablen =

Das Plugin definiert CSS Custom Properties für konsistentes Styling:

```css
--soulmakers-primary: #2c3e50
--soulmakers-secondary: #3498db
--soulmakers-accent: #e74c3c
--soulmakers-success: #27ae60
--soulmakers-warning: #f39c12
--soulmakers-error: #e74c3c
```

= Entwicklung =

**Neue Admin-Unterseite hinzufügen:**

```php
add_action('admin_menu', function() {
    add_submenu_page(
        'soulmakers',                          // Parent-Slug
        __('Neue Seite', 'soulmakers'),        // Seitentitel
        __('Neue Seite', 'soulmakers'),        // Menütitel
        'manage_options',                       // Berechtigung
        'soulmakers-neue-seite',               // Menü-Slug
        'meine_callback_funktion'              // Callback
    );
});
```

**AJAX-Action registrieren:**

```php
add_action('wp_ajax_soulmakers_meine_action', 'meine_ajax_handler');
add_action('wp_ajax_nopriv_soulmakers_meine_action', 'meine_ajax_handler');

function meine_ajax_handler() {
    check_ajax_referer('soulmakers_frontend_nonce', 'nonce');
    // Logik hier
    wp_send_json_success(['message' => 'Erfolg']);
}
```

= Externe Libraries =

**SweetAlert2 (v11.14.0)**
Schöne, responsive, anpassbare Dialoge.
Dokumentation: https://sweetalert2.github.io/

**Select2 (v4.1.0)**
Erweiterte Dropdown-Auswahl mit Suche.
Dokumentation: https://select2.org/

== Installation ==

1. Lade den `soulmakers`-Ordner in das `/wp-content/plugins/`-Verzeichnis hoch
2. Aktiviere das Plugin über das 'Plugins'-Menü in WordPress
3. Konfiguriere das Plugin unter 'Soulmakers' im Admin-Menü

== Changelog ==

= 1.0.0 =
* Erste Version
* Plugin-Grundstruktur erstellt
* Admin-Menü implementiert
* Frontend- und Backend-Assets eingebunden
* SweetAlert2 und Select2 integriert

== Upgrade Notice ==

= 1.0.0 =
Erste Version des Soulmakers Plugins.
