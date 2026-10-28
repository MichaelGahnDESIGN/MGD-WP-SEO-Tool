<?php
/**
 * Plugin Name: MGD WP-SEO-Tool
 * Plugin URI: https://github.com/MichaelGahnDESIGN/MGD-WP-SEO-Tool
 * Description: Ein datensparsames Werkzeug für Meta-Titel, Meta-Beschreibungen und Suchvorschauen – direkt innerhalb von WordPress nutzbar.
 * Version: 0.1.1
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: Michael Gahn DESIGN
 * Author URI: https://michael-gahn.de/
 * Text Domain: mgd-wp-seo-tool
 * Update URI: https://github.com/MichaelGahnDESIGN/MGD-WP-SEO-Tool
 *
 * Das Plugin verarbeitet Eingaben ausschließlich im Browser. Nur der bewusst
 * ausgelöste Social-Abruf fragt die eingegebene öffentliche HTTPS-URL ab.
 */

defined( 'ABSPATH' ) || exit;

define( 'MGD_WP_SEO_TOOL_VERSION', '0.1.1' );
define( 'MGD_WP_SEO_TOOL_FILE', __FILE__ );
define( 'MGD_WP_SEO_TOOL_DIR', plugin_dir_path( __FILE__ ) );
define( 'MGD_WP_SEO_TOOL_URL', plugin_dir_url( __FILE__ ) );

require_once MGD_WP_SEO_TOOL_DIR . 'includes/class-mgd-wp-seo-tool.php';
require_once MGD_WP_SEO_TOOL_DIR . 'includes/class-mgd-wp-seo-social-fetcher.php';
require_once MGD_WP_SEO_TOOL_DIR . 'includes/class-mgd-plugin-admin-tools.php';

MGD_WP_SEO_Tool::instance();

/**
 * Die Updatequelle ist absichtlich vollständig festgelegt. Dadurch kann kein
 * WordPress-Admin eine beliebige Download-URL hinterlegen und die Website
 * lädt weder Tokens noch Zugangsdaten in das Plugin.
 */
MGD_WP_SEO_Plugin_Admin_Tools::register(
	array(
		'file'        => __FILE__,
		'repo'        => 'MGD-WP-SEO-Tool',
		'slug'        => 'mgd-wp-seo-tool',
		'name'        => 'MGD WP-SEO-Tool',
		'description' => 'Datensparsame Meta-Snippet- und Social-Vorschau direkt in WordPress und Divi.',
		'version'     => MGD_WP_SEO_TOOL_VERSION,
		'settings'    => 'plugins.php',
		'requires'    => '6.5',
		'php'         => '8.1',
		'package'     => 'mgd-wp-seo-tool-{version}.zip',
	)
);
