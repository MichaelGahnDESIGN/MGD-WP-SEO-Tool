<?php
/**
 * Frontend-Integration des MGD WP-SEO-Tools.
 *
 * Die Darstellung wird bewusst über einen Shortcode bereitgestellt. Dadurch
 * bleibt das Tool sowohl in Divi (Text-/Code-Modul) als auch im Block-Editor
 * ohne proprietäre Builder-Abhängigkeit nutzbar.
 */

defined( 'ABSPATH' ) || exit;

final class MGD_WP_SEO_Tool {

	/** @var self|null Singleton-Instanz. */
	private static ?self $instance = null;

	/**
	 * Gibt die einzige Plugin-Instanz zurück.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Registriert ausschließlich öffentliche, nicht persistierende Funktionen.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_shortcode( 'mgd_wp_seo_tool', array( $this, 'render_shortcode' ) );
		MGD_WP_SEO_Social_Fetcher::instance();
	}

	/**
	 * Ermöglicht später eine Übersetzung, ohne das Plugin an eine Sprachdatei zu binden.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'mgd-wp-seo-tool',
			false,
			dirname( plugin_basename( MGD_WP_SEO_TOOL_FILE ) ) . '/languages'
		);
	}

	/**
	 * Lädt CSS und JavaScript nur auf Seiten, auf denen der Shortcode tatsächlich
	 * ausgegeben wird. Dies vermeidet unnötiges JavaScript auf der restlichen Website.
	 */
	private function enqueue_assets(): void {
		wp_enqueue_style(
			'mgd-wp-seo-tool',
			MGD_WP_SEO_TOOL_URL . 'assets/css/seo-tool.css',
			array(),
			MGD_WP_SEO_TOOL_VERSION
		);

		wp_enqueue_script(
			'mgd-wp-seo-tool',
			MGD_WP_SEO_TOOL_URL . 'assets/js/seo-tool.js',
			array(),
			MGD_WP_SEO_TOOL_VERSION,
			true
		);

		wp_localize_script(
			'mgd-wp-seo-tool',
			'mgdSeoToolConfig',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'socialNonce' => wp_create_nonce( 'mgd_wp_seo_social_preview' ),
			)
		);
	}

	/**
	 * Rendert das Tool. Es gibt keine AJAX-Route und keine Datenbankoperation:
	 * Titel, Beschreibung und URL verlassen den Browser nicht.
	 *
	 * @param array<string, mixed> $attributes Shortcode-Attribute.
	 */
	public function render_shortcode( array $attributes = array() ): string {
		$attributes = shortcode_atts(
			array(
				'url' => home_url( '/' ),
			),
			$attributes,
			'mgd_wp_seo_tool'
		);

		$this->enqueue_assets();

		$instance_id = wp_unique_id( 'mgd-seo-tool-' );
		$default_url = esc_url( $attributes['url'] );

		ob_start();
		require MGD_WP_SEO_TOOL_DIR . 'templates/seo-tool.php';

		return (string) ob_get_clean();
	}
}
