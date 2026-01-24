<?php
/**
 * Plugin-Integrationen für Soulmakers
 *
 * Handhabt Integrationen mit Drittanbieter-Plugins wie ACF, Elementor, etc.
 *
 * @package Soulmakers
 * @since   1.0.0
 */

// Direkten Zugriff verhindern
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Soulmakers_Integrations
 */
class Soulmakers_Integrations {

    /**
     * Google Maps API Key
     *
     * @var string
     */
    private string $google_maps_api_key = 'AIzaSyCg-vN8w-dxaDZUO31m0TATff2s_r1KC8M';

    /**
     * Konstruktor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Hooks initialisieren
     */
    private function init_hooks(): void {
        // ACF Pro Integration
        add_action( 'acf/init', array( $this, 'acf_init' ) );
    }

    /**
     * ACF Pro initialisieren
     *
     * Setzt Einstellungen für Advanced Custom Fields Pro
     */
    public function acf_init(): void {
        // Google Maps API Key für ACF setzen
        if ( function_exists( 'acf_update_setting' ) ) {
            acf_update_setting( 'google_api_key', $this->google_maps_api_key );
        }
    }
}
