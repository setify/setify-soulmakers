<?php
/**
 * Admin-Funktionalität des Soulmakers Plugins
 *
 * @package Soulmakers
 * @since   1.0.0
 */

// Direkten Zugriff verhindern
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Soulmakers_Admin
 */
class Soulmakers_Admin {

    /**
     * Konstruktor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Admin-Hooks initialisieren
     */
    private function init_hooks(): void {
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Admin-Menü registrieren
     */
    public function register_admin_menu(): void {
        add_menu_page(
            __( 'Soulmakers', 'soulmakers' ),
            __( 'Soulmakers', 'soulmakers' ),
            'manage_options',
            'soulmakers',
            array( $this, 'render_admin_page' ),
            'dashicons-heart',
            30
        );
    }

    /**
     * Haupt-Admin-Seite rendern
     */
    public function render_admin_page(): void {
        ?>
        <div class="wrap soulmakers-admin">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Willkommen bei Soulmakers.', 'soulmakers' ); ?></p>
        </div>
        <?php
    }

    /**
     * Admin-Styles einbinden
     *
     * @param string $hook_suffix Aktueller Admin-Seiten-Hook.
     */
    public function enqueue_styles( string $hook_suffix ): void {
        // Admin-CSS auf allen Admin-Seiten laden
        wp_enqueue_style(
            'soulmakers-admin',
            SOULMAKERS_ASSETS_URL . 'css/admin.css',
            array(),
            SOULMAKERS_VERSION
        );

        // Select2 CSS
        wp_enqueue_style(
            'soulmakers-select2',
            SOULMAKERS_ASSETS_URL . 'vendor/select2/select2.min.css',
            array(),
            '4.1.0'
        );
    }

    /**
     * Admin-Scripts einbinden
     *
     * @param string $hook_suffix Aktueller Admin-Seiten-Hook.
     */
    public function enqueue_scripts( string $hook_suffix ): void {
        // Nur auf Soulmakers-Admin-Seiten laden
        if ( strpos( $hook_suffix, 'soulmakers' ) === false ) {
            return;
        }

        // SweetAlert2
        wp_enqueue_script(
            'soulmakers-sweetalert2',
            SOULMAKERS_ASSETS_URL . 'vendor/sweetalert2/sweetalert2.min.js',
            array(),
            '11.14.0',
            true
        );

        // Select2
        wp_enqueue_script(
            'soulmakers-select2',
            SOULMAKERS_ASSETS_URL . 'vendor/select2/select2.min.js',
            array( 'jquery' ),
            '4.1.0',
            true
        );

        // Admin JS
        wp_enqueue_script(
            'soulmakers-admin',
            SOULMAKERS_ASSETS_URL . 'js/admin.js',
            array( 'jquery', 'soulmakers-sweetalert2', 'soulmakers-select2' ),
            SOULMAKERS_VERSION,
            true
        );

        // Lokalisierte Daten für JS
        wp_localize_script(
            'soulmakers-admin',
            'soulmakersAdmin',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'soulmakers_admin_nonce' ),
                'i18n'    => array(
                    'confirm' => __( 'Bestätigen', 'soulmakers' ),
                    'cancel'  => __( 'Abbrechen', 'soulmakers' ),
                    'success' => __( 'Erfolgreich', 'soulmakers' ),
                    'error'   => __( 'Fehler', 'soulmakers' ),
                ),
            )
        );
    }
}
