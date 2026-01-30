<?php
/**
 * REST API für Soulmakers
 *
 * @package Soulmakers
 * @since   1.0.0
 */

// Direkten Zugriff verhindern
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Soulmakers_Api
 */
class Soulmakers_Api {

    /**
     * API Namespace
     *
     * @var string
     */
    private string $namespace = 'soulmakers/v1';

    /**
     * Konstruktor
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * REST-Routen registrieren
     */
    public function register_routes(): void {
        register_rest_route(
            $this->namespace,
            '/create-soulmaker',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'create_soulmaker' ),
                'permission_callback' => array( $this, 'check_api_key' ),
            )
        );
    }

    /**
     * API-Key validieren
     *
     * @param WP_REST_Request $request Request-Objekt.
     * @return bool|WP_Error
     */
    public function check_api_key( WP_REST_Request $request ) {
        $api_key = $request->get_header( 'X-API-Key' );

        if ( empty( $api_key ) ) {
            return new WP_Error(
                'missing_api_key',
                __( 'API-Key fehlt', 'soulmakers' ),
                array( 'status' => 401 )
            );
        }

        if ( ! defined( 'SOULMAKERS_API_KEY' ) || $api_key !== SOULMAKERS_API_KEY ) {
            return new WP_Error(
                'invalid_api_key',
                __( 'Ungültiger API-Key', 'soulmakers' ),
                array( 'status' => 403 )
            );
        }

        return true;
    }

    /**
     * Soulmaker-Post erstellen
     *
     * @param WP_REST_Request $request Request-Objekt.
     * @return WP_REST_Response|WP_Error
     */
    public function create_soulmaker( WP_REST_Request $request ) {
        $params = $request->get_json_params();

        // Pflichtfelder prüfen
        $required = array( 'firstname', 'lastname', 'business_name', 'email' );
        foreach ( $required as $field ) {
            if ( empty( $params[ $field ] ) ) {
                return new WP_Error(
                    'missing_field',
                    sprintf( __( 'Pflichtfeld fehlt: %s', 'soulmakers' ), $field ),
                    array( 'status' => 400 )
                );
            }
        }

        // Post-Titel erstellen
        $post_title = sprintf(
            '%s - %s %s',
            sanitize_text_field( $params['business_name'] ),
            sanitize_text_field( $params['firstname'] ),
            sanitize_text_field( $params['lastname'] )
        );

        // Post-Slug erstellen (vorname-nachname)
        $post_slug = sanitize_title(
            $params['firstname'] . '-' . $params['lastname']
        );

        // Post-Daten vorbereiten
        $post_data = array(
            'post_title'  => $post_title,
            'post_name'   => $post_slug,
            'post_type'   => 'soulmaker',
            'post_status' => 'publish',
        );

        // Author setzen (falls vorhanden)
        if ( ! empty( $params['user_id'] ) ) {
            $user_id = absint( $params['user_id'] );
            if ( get_user_by( 'ID', $user_id ) ) {
                $post_data['post_author'] = $user_id;
            }
        }

        // Datum setzen (falls vorhanden)
        if ( ! empty( $params['created_at'] ) ) {
            $post_data['post_date'] = sanitize_text_field( $params['created_at'] );
        }

        // Post erstellen
        $post_id = wp_insert_post( $post_data, true );

        if ( is_wp_error( $post_id ) ) {
            return new WP_Error(
                'post_creation_failed',
                $post_id->get_error_message(),
                array( 'status' => 500 )
            );
        }

        // Taxonomie "space" zuweisen (falls Term-ID übergeben)
        if ( ! empty( $params['space'] ) ) {
            $term_id = absint( $params['space'] );
            if ( $term_id > 0 ) {
                wp_set_object_terms( $post_id, $term_id, 'space' );
            }
        }

        // ACF-Felder speichern
        $this->save_acf_fields( $post_id, $params );

        // E-Mail an Soulmaker senden
        $this->send_profile_created_email( $params, $post_id );

        return new WP_REST_Response(
            array(
                'success' => true,
                'post_id' => $post_id,
                'message' => __( 'Soulmaker erfolgreich erstellt', 'soulmakers' ),
            ),
            201
        );
    }

    /**
     * E-Mail nach Profilerstellung senden
     *
     * @param array $params  Request-Parameter.
     * @param int   $post_id Post-ID für URL-Generierung.
     */
    private function send_profile_created_email( array $params, int $post_id ): void {
        $email         = sanitize_email( $params['email'] ?? '' );
        $firstname     = sanitize_text_field( $params['firstname'] ?? '' );
        $lastname      = sanitize_text_field( $params['lastname'] ?? '' );
        $business_name = sanitize_text_field( $params['business_name'] ?? '' );

        if ( empty( $email ) ) {
            return;
        }

        Soulmakers_Mail::instance()->send_profile_created( $email, $firstname, $lastname, $email, $business_name, $post_id );
    }

    /**
     * ACF-Felder speichern
     *
     * @param int   $post_id Post-ID.
     * @param array $params  Request-Parameter.
     */
    private function save_acf_fields( int $post_id, array $params ): void {
        // Text-Felder
        $text_fields = array(
            'soulmaker_firstname'        => 'firstname',
            'soulmaker_lastname'         => 'lastname',
            'soulmaker_name'             => 'business_name',
            'soulmaker_phone'            => 'phone',
            'soulmaker_email'            => 'email',
            'soulmaker_website'          => 'url',
            'soulmaker_social_instagram' => 'instagram',
            'soulmaker_social_tiktok'    => 'tiktok',
            'soulmaker_social_facebook'  => 'facebook',
        );

        foreach ( $text_fields as $acf_field => $param_key ) {
            if ( ! empty( $params[ $param_key ] ) ) {
                update_field( $acf_field, sanitize_text_field( $params[ $param_key ] ), $post_id );
            }
        }

        // Bild-Felder (URL → Attachment)
        if ( ! empty( $params['avatar'] ) ) {
            $avatar_id = $this->upload_image_from_url( $params['avatar'], $post_id );
            if ( $avatar_id ) {
                update_field( 'soulmaker_avatar', $avatar_id, $post_id );
            }
        }

        if ( ! empty( $params['logo'] ) ) {
            $logo_id = $this->upload_image_from_url( $params['logo'], $post_id );
            if ( $logo_id ) {
                update_field( 'soulmaker_logo', $logo_id, $post_id );
            }
        }
    }

    /**
     * Bild von URL herunterladen und als Attachment speichern
     *
     * @param string $url     Bild-URL.
     * @param int    $post_id Post-ID für Attachment.
     * @return int|false Attachment-ID oder false bei Fehler.
     */
    private function upload_image_from_url( string $url, int $post_id ) {
        // WordPress Medien-Funktionen laden
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // URL validieren
        $url = esc_url_raw( $url );
        if ( empty( $url ) ) {
            return false;
        }

        // Prüfen ob Bild bereits in Medien existiert (gleiche URL)
        $existing = $this->get_attachment_by_url( $url );
        if ( $existing ) {
            return $existing;
        }

        // Bild herunterladen und als Attachment speichern
        $attachment_id = media_sideload_image( $url, $post_id, null, 'id' );

        if ( is_wp_error( $attachment_id ) ) {
            return false;
        }

        return $attachment_id;
    }

    /**
     * Attachment-ID anhand der URL finden
     *
     * @param string $url Bild-URL.
     * @return int|false Attachment-ID oder false.
     */
    private function get_attachment_by_url( string $url ) {
        global $wpdb;

        // Dateiname aus URL extrahieren
        $filename = basename( parse_url( $url, PHP_URL_PATH ) );

        $attachment_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid LIKE %s",
                '%' . $wpdb->esc_like( $filename )
            )
        );

        return $attachment_id ? (int) $attachment_id : false;
    }
}
