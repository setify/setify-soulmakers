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
}
