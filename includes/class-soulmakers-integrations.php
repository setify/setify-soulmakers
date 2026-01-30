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

        // Soulmaker-Titel automatisch generieren (Priorität 20: nach ACF-Speicherung)
        add_action( 'acf/save_post', array( $this, 'update_soulmaker_title' ), 20 );
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

    /**
     * Soulmaker-Titel automatisch aus ACF-Feldern generieren
     *
     * Format: "soulmaker_name - soulmaker_firstname soulmaker_lastname"
     *
     * @param int $post_id Post-ID.
     */
    public function update_soulmaker_title( int $post_id ): void {
        // Keine Revisions oder Autosaves
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        // Nur für Post-Type "soulmaker"
        if ( get_post_type( $post_id ) !== 'soulmaker' ) {
            return;
        }

        // ACF-Felder auslesen
        $name      = get_field( 'soulmaker_name', $post_id );
        $firstname = get_field( 'soulmaker_firstname', $post_id );
        $lastname  = get_field( 'soulmaker_lastname', $post_id );

        // Mindestens ein Feld muss gefüllt sein
        if ( empty( $name ) && empty( $firstname ) && empty( $lastname ) ) {
            return;
        }

        // Titel zusammenbauen
        $title_parts = array();

        if ( ! empty( $name ) ) {
            $title_parts[] = $name;
        }

        $fullname = trim( $firstname . ' ' . $lastname );
        if ( ! empty( $fullname ) ) {
            $title_parts[] = $fullname;
        }

        $new_title = implode( ' - ', $title_parts );

        // Slug generieren (vorname-nachname)
        $new_slug = sanitize_title( trim( $firstname . '-' . $lastname ) );

        // Aktuellen Titel prüfen (verhindert Endlosschleife)
        $current_post = get_post( $post_id );
        if ( $current_post->post_title === $new_title && $current_post->post_name === $new_slug ) {
            return;
        }

        // Hook temporär entfernen (verhindert Endlosschleife)
        remove_action( 'acf/save_post', array( $this, 'update_soulmaker_title' ), 20 );

        // Post aktualisieren
        wp_update_post( array(
            'ID'         => $post_id,
            'post_title' => $new_title,
            'post_name'  => $new_slug,
        ) );

        // Hook wieder hinzufügen
        add_action( 'acf/save_post', array( $this, 'update_soulmaker_title' ), 20 );
    }
}
