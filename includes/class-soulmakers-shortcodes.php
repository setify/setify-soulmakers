<?php
/**
 * Shortcodes für Soulmakers
 *
 * @package Soulmakers
 * @since   1.0.0
 */

// Direkten Zugriff verhindern
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Soulmakers_Shortcodes
 */
class Soulmakers_Shortcodes {

    /**
     * Konstruktor
     */
    public function __construct() {
        $this->register_shortcodes();
    }

    /**
     * Shortcodes registrieren
     */
    private function register_shortcodes(): void {
        add_shortcode( 'soulmaker_owner', array( $this, 'shortcode_soulmaker_owner' ) );
        add_shortcode( 'soulmaker_access', array( $this, 'shortcode_soulmaker_access' ) );
    }

    /**
     * Shortcode: Inhalt nur für Admin oder Post-Autor anzeigen
     *
     * Verwendung: [soulmaker_owner]Geschützter Inhalt[/soulmaker_owner]
     *
     * @param array  $atts    Shortcode-Attribute.
     * @param string $content Eingeschlossener Inhalt.
     * @return string
     */
    public function shortcode_soulmaker_owner( array $atts, string $content = null ): string {
        // Kein Inhalt = nichts ausgeben
        if ( empty( $content ) ) {
            return '';
        }

        // Nicht eingeloggt = nichts ausgeben
        if ( ! is_user_logged_in() ) {
            return '';
        }

        // Nur für Post-Type "soulmaker"
        if ( get_post_type() !== 'soulmaker' ) {
            return '';
        }

        $current_user_id = get_current_user_id();
        $post_author_id  = (int) get_post_field( 'post_author', get_the_ID() );

        // Admin oder Post-Autor?
        if ( current_user_can( 'administrator' ) || $current_user_id === $post_author_id ) {
            return do_shortcode( $content );
        }

        return '';
    }

    /**
     * Shortcode: Prüft ob URL-Parameter mit ACF-Feld übereinstimmt
     *
     * Verwendung: [soulmaker_access]Geschützter Inhalt[/soulmaker_access]
     *
     * @param array  $atts    Shortcode-Attribute.
     * @param string $content Eingeschlossener Inhalt.
     * @return string
     */
    public function shortcode_soulmaker_access( array $atts, string $content = null ): string {
        // ACF-Feld auslesen
        $stored_code = get_field( 'global_access_code', get_the_ID() );

        // Kein Code im ACF-Feld = nichts ausgeben
        if ( empty( $stored_code ) ) {
            return '';
        }

        // URL-Parameter auslesen
        $url_code = isset( $_GET['code'] ) ? sanitize_text_field( $_GET['code'] ) : '';

        // Codes vergleichen
        if ( $stored_code === $url_code ) {
            return empty( $content ) ? 'true' : do_shortcode( $content );
        }

        return '';
    }
}
