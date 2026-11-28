<?php
/**
 * Plugin Name: MGD WP-SEO-Tool
 * Plugin URI: https://theme.michael-gahn.de/plugins/plugin-wp-seo-tool-wordpress
 * Description: Meta-Titel und Meta-Beschreibung prüfen, mit Google- und Social-Vorschau.
 * Version: 2.2.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: Michael Gahn DESIGN
 * Author URI: https://michael-gahn.de/
 * Text Domain: mgd-wp-seo-tool
 */

defined( 'ABSPATH' ) || exit;

define( 'MGD_WP_SEO_TOOL_VERSION', '2.2.0' );
define( 'MGD_WP_SEO_TOOL_FILE', __FILE__ );
define( 'MGD_WP_SEO_TOOL_DIR', plugin_dir_path( __FILE__ ) );
define( 'MGD_WP_SEO_TOOL_URL', plugin_dir_url( __FILE__ ) );

require_once MGD_WP_SEO_TOOL_DIR . 'includes/class-mgd-wp-seo-tool.php';
require_once MGD_WP_SEO_TOOL_DIR . 'includes/class-mgd-wp-seo-page-analyzer.php';
require_once MGD_WP_SEO_TOOL_DIR . 'includes/class-mgd-wp-seo-social-fetcher.php';

MGD_WP_SEO_Tool::instance();

// Baukasten aus MichaThemeV5-Pro > includes/Kit (bin/build-plugin-kit.php)
spl_autoload_register( static function ( string $class ): void {
	$prefix = 'Mgd\\WpSeoTool\\Kit\\';
	if ( str_starts_with( $class, $prefix ) ) {
		$file = MGD_WP_SEO_TOOL_DIR . 'includes/Kit/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_file( $file ) ) {
			require $file;
		}
	}
} );

// Updater + MGD Tools vom Baukasten (kein eigenes Menü, nur Shortcode)
use Mgd\WpSeoTool\Kit\Platform\WordPress\Setup;
use Mgd\WpSeoTool\Kit\Platform\WordPress\Tools;

$mgd_wp_seo_updater = Setup::updater( __FILE__, 'mgd-wp-seo-tool-wordpress', 'wordpress', MGD_WP_SEO_TOOL_VERSION, 'MGD WP-SEO-Tool', Tools::url() );
// Reste vom alten Updater wegräumen, 1x pro Version
Setup::cleanupLegacy( 'mgd-wp-seo-tool-wordpress', MGD_WP_SEO_TOOL_VERSION, array() );
Setup::tools( __FILE__, 'mgd-wp-seo-tool-wordpress', 'wordpress', MGD_WP_SEO_TOOL_VERSION, 'MGD WP-SEO-Tool', $mgd_wp_seo_updater, array(
	'slug'    => 'plugin-wp-seo-tool-wordpress',
	'repo'    => 'MGD-WP-SEO-Tool',
	'license' => false,
) );
