<?php
/**
 * Plugin Name:       Soulmakers
 * Plugin URI:        https://soulmakers.de
 * Description:       Soulmakers WordPress Plugin - Erweiterte Funktionalitäten für die Soulmakers Website.
 * Version:           1.0.0
 * Author:            Setify
 * Author URI:        https://setify.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       soulmakers
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 */

// Direkten Zugriff verhindern
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin-Konstanten definieren
 */
define( 'SOULMAKERS_VERSION', '1.0.0' );
define( 'SOULMAKERS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SOULMAKERS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SOULMAKERS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SOULMAKERS_ASSETS_URL', SOULMAKERS_PLUGIN_URL . 'assets/' );

/**
 * Autoloader für Plugin-Klassen
 */
spl_autoload_register( function( $class ) {
    $prefix = 'Soulmakers_';

    if ( strpos( $class, $prefix ) !== 0 ) {
        return;
    }

    $class_name = str_replace( $prefix, '', $class );
    $class_name = strtolower( str_replace( '_', '-', $class_name ) );
    $file = SOULMAKERS_PLUGIN_DIR . 'includes/class-soulmakers-' . $class_name . '.php';

    if ( file_exists( $file ) ) {
        require_once $file;
    }
});

/**
 * Hauptklasse laden
 */
require_once SOULMAKERS_PLUGIN_DIR . 'includes/class-soulmakers.php';

/**
 * Plugin-Instanz zurückgeben
 *
 * @return Soulmakers
 */
function soulmakers() {
    return Soulmakers::instance();
}

// Plugin starten
soulmakers();
