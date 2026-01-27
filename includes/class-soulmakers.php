<?php
/**
 * Hauptklasse des Soulmakers Plugins
 *
 * @package Soulmakers
 * @since   1.0.0
 */

// Direkten Zugriff verhindern
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Soulmakers
 */
final class Soulmakers {

    /**
     * Plugin-Instanz
     *
     * @var Soulmakers|null
     */
    private static ?Soulmakers $instance = null;

    /**
     * Admin-Instanz
     *
     * @var Soulmakers_Admin|null
     */
    public ?Soulmakers_Admin $admin = null;

    /**
     * Frontend-Instanz
     *
     * @var Soulmakers_Frontend|null
     */
    public ?Soulmakers_Frontend $frontend = null;

    /**
     * Integrations-Instanz
     *
     * @var Soulmakers_Integrations|null
     */
    public ?Soulmakers_Integrations $integrations = null;

    /**
     * Singleton-Instanz zurückgeben
     *
     * @return Soulmakers
     */
    public static function instance(): Soulmakers {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Konstruktor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Abhängigkeiten laden
     */
    private function load_dependencies(): void {
        require_once SOULMAKERS_PLUGIN_DIR . 'includes/class-soulmakers-admin.php';
        require_once SOULMAKERS_PLUGIN_DIR . 'includes/class-soulmakers-frontend.php';
        require_once SOULMAKERS_PLUGIN_DIR . 'includes/class-soulmakers-integrations.php';
    }

    /**
     * Hooks initialisieren
     */
    private function init_hooks(): void {
        add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Nach dem Laden aller Plugins initialisieren
     */
    public function on_plugins_loaded(): void {
        $this->load_textdomain();

        // Admin initialisieren
        if ( is_admin() ) {
            $this->admin = new Soulmakers_Admin();
        }

        // Frontend initialisieren (auch im Admin für AJAX)
        $this->frontend = new Soulmakers_Frontend();

        // Plugin-Integrationen initialisieren
        $this->integrations = new Soulmakers_Integrations();
    }

    /**
     * Plugin initialisieren
     */
    public function init(): void {
        do_action( 'soulmakers_init' );
    }

    /**
     * Textdomain laden
     */
    public function load_textdomain(): void {
        load_plugin_textdomain(
            'soulmakers',
            false,
            dirname( SOULMAKERS_PLUGIN_BASENAME ) . '/languages/'
        );
    }

    /**
     * Klonen verhindern
     */
    private function __clone() {}

    /**
     * Unserialisierung verhindern
     */
    public function __wakeup() {
        throw new \Exception( 'Soulmakers kann nicht unserialisiert werden.' );
    }
}
