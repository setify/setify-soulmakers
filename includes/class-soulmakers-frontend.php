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
        add_action( 'template_redirect', array( $this, 'redirect_to_profile' ) );

        // Soulmaker-Rolle: Admin-Bereich sperren
        add_filter( 'show_admin_bar', array( $this, 'hide_admin_bar_for_soulmakers' ) );
        add_action( 'admin_init', array( $this, 'block_admin_for_soulmakers' ) );

        // SVG-IDs eindeutig machen (verhindert Clip-Path-Konflikte)
        add_filter( 'the_content', array( $this, 'make_svg_ids_unique_in_content' ), 99 );
        add_filter( 'elementor/frontend/the_content', array( $this, 'make_svg_ids_unique_in_content' ), 99 );
    }

    /**
     * SVG-Zähler für eindeutige IDs
     *
     * @var int
     */
    private static int $svg_counter = 0;

    /**
     * SVG-IDs im Content eindeutig machen
     *
     * @param string $content Der Content.
     * @return string
     */
    public function make_svg_ids_unique_in_content( string $content ): string {
        return preg_replace_callback(
            '/<svg[^>]*>.*?<\/svg>/s',
            array( $this, 'make_svg_ids_unique' ),
            $content
        );
    }

    /**
     * Einzelnes SVG: IDs eindeutig machen
     *
     * @param array $matches Regex-Matches.
     * @return string
     */
    private function make_svg_ids_unique( array $matches ): string {
        self::$svg_counter++;
        $svg_content = $matches[0];
        $counter     = self::$svg_counter;

        // IDs eindeutig machen
        $svg_content = preg_replace_callback(
            '/id="([^"]+)"/',
            function ( $id_matches ) use ( $counter ) {
                return 'id="' . $id_matches[1] . '-' . $counter . '"';
            },
            $svg_content
        );

        // Referenzen aktualisieren (url(#...))
        $svg_content = preg_replace_callback(
            '/url\(#([^)]+)\)/',
            function ( $url_matches ) use ( $counter ) {
                return 'url(#' . $url_matches[1] . '-' . $counter . ')';
            },
            $svg_content
        );

        // xlink:href Referenzen aktualisieren
        $svg_content = preg_replace_callback(
            '/xlink:href="#([^"]+)"/',
            function ( $href_matches ) use ( $counter ) {
                return 'xlink:href="#' . $href_matches[1] . '-' . $counter . '"';
            },
            $svg_content
        );

        // href Referenzen aktualisieren (moderne SVGs)
        $svg_content = preg_replace_callback(
            '/href="#([^"]+)"/',
            function ( $href_matches ) use ( $counter ) {
                return 'href="#' . $href_matches[1] . '-' . $counter . '"';
            },
            $svg_content
        );

        return $svg_content;
    }

    /**
     * Admin-Bar für Soulmaker-Rolle ausblenden
     *
     * @param bool $show Ob Admin-Bar angezeigt werden soll.
     * @return bool
     */
    public function hide_admin_bar_for_soulmakers( bool $show ): bool {
        if ( $this->user_is_soulmaker_only() ) {
            return false;
        }
        return $show;
    }

    /**
     * Admin-Bereich für Soulmaker-Rolle sperren
     */
    public function block_admin_for_soulmakers(): void {
        if ( wp_doing_ajax() ) {
            return;
        }

        if ( $this->user_is_soulmaker_only() ) {
            wp_redirect( home_url( '/redirect-to-profile' ) );
            exit;
        }
    }

    /**
     * Prüft ob der User nur die Soulmaker-Rolle hat (keine höheren Rechte)
     *
     * @return bool
     */
    private function user_is_soulmaker_only(): bool {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $user = wp_get_current_user();

        // Hat Soulmaker-Rolle und keine Admin-Rechte
        return in_array( 'soulmaker', (array) $user->roles, true )
            && ! current_user_can( 'edit_posts' );
    }

    /**
     * Weiterleitung zum eigenen Soulmaker-Profil
     */
    public function redirect_to_profile(): void {
        // Nur für /redirect-to-profile
        $request_uri = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
        if ( $request_uri !== 'redirect-to-profile' ) {
            return;
        }

        // Nicht eingeloggt → zur Login-Seite
        if ( ! is_user_logged_in() ) {
            wp_redirect( wp_login_url( home_url( '/redirect-to-profile' ) ) );
            exit;
        }

        // Soulmaker-Post des aktuellen Benutzers finden
        $user_id = get_current_user_id();
        $posts   = get_posts( array(
            'post_type'      => 'soulmaker',
            'author'         => $user_id,
            'posts_per_page' => 1,
            'post_status'    => 'publish',
        ) );

        if ( ! empty( $posts ) ) {
            wp_redirect( get_permalink( $posts[0]->ID ) );
            exit;
        }

        // Kein Profil gefunden → zur Startseite
        wp_redirect( home_url() );
        exit;
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
