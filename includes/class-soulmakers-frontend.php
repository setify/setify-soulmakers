<?php
/**
 * Frontend-Funktionalität des Soulmakers Plugins
 *
 * @package Soulmakers
 * @since   1.0.0
 */

// Direkten Zugriff verhindern
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Soulmakers_Frontend
 */
class Soulmakers_Frontend {

    /**
     * Konstruktor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Frontend-Hooks initialisieren
     */
    private function init_hooks(): void {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Frontend-Styles einbinden
     */
    public function enqueue_styles(): void {
        // Frontend-CSS auf allen Seiten laden
        wp_enqueue_style(
            'soulmakers-frontend',
            SOULMAKERS_ASSETS_URL . 'css/frontend.css',
            array(),
            SOULMAKERS_VERSION
        );

        // Vendor-Libraries nur registrieren (laden bei Bedarf via wp_enqueue_style)
        wp_register_style(
            'soulmakers-select2',
            SOULMAKERS_ASSETS_URL . 'vendor/select2/select2.min.css',
            array(),
            '4.1.0'
        );
    }

    /**
     * Frontend-Scripts einbinden
     */
    public function enqueue_scripts(): void {
        // Vendor-Libraries nur registrieren (laden bei Bedarf via wp_enqueue_script)
        wp_register_script(
            'soulmakers-sweetalert2',
            SOULMAKERS_ASSETS_URL . 'vendor/sweetalert2/sweetalert2.min.js',
            array(),
            '11.14.0',
            true
        );

        wp_register_script(
            'soulmakers-select2',
            SOULMAKERS_ASSETS_URL . 'vendor/select2/select2.min.js',
            array( 'jquery' ),
            '4.1.0',
            true
        );

        // Frontend JS - nur jQuery als Abhängigkeit, Vendor-Libraries bei Bedarf
        wp_enqueue_script(
            'soulmakers-frontend',
            SOULMAKERS_ASSETS_URL . 'js/frontend.js',
            array( 'jquery' ),
            SOULMAKERS_VERSION,
            true
        );

        // Lokalisierte Daten für JS
        wp_localize_script(
            'soulmakers-frontend',
            'soulmakersFrontend',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'soulmakers_frontend_nonce' ),
                'version' => SOULMAKERS_VERSION,
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
