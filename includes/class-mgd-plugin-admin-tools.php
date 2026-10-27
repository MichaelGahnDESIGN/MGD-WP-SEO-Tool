<?php
/**
 * Sichere GitHub-Release-Integration für das MGD WP-SEO-Tool.
 *
 * Die Klasse fragt ausschließlich die fest codierte öffentliche GitHub-Quelle
 * ab. Sie überträgt weder Inhalte aus dem SEO-Tool noch Zugangsdaten; GitHub
 * erhält bei einer Updateprüfung lediglich die Server-IP der WordPress-Site.
 */

defined( 'ABSPATH' ) || exit;

final class MGD_WP_SEO_Plugin_Admin_Tools {

	/** @var array<string, array<string, string>> Registrierte Plugin-Metadaten. */
	private static array $plugins = array();

	/**
	 * Registriert die zentrale Updatequelle einmal pro Request.
	 *
	 * @param array<string, string> $plugin Nicht geheime Plugin-Metadaten.
	 */
	public static function register( array $plugin ): void {
		if ( empty( self::$plugins ) ) {
			add_action( 'admin_notices', array( self::class, 'render_update_notice' ) );
			add_action( 'admin_post_mgd_wp_seo_check_updates', array( self::class, 'check_updates' ) );
			add_filter( 'plugin_row_meta', array( self::class, 'add_plugin_links' ), 99, 2 );
			add_filter( 'plugins_api', array( self::class, 'plugin_information' ), 99, 3 );
			add_filter( 'update_plugins_github.com', array( self::class, 'offer_update' ), 30, 3 );
		}

		self::$plugins[ plugin_basename( $plugin['file'] ) ] = $plugin;
	}

	/**
	 * Zeigt nur auf den Standard-Update-Seiten einen manuellen Prüfknopf.
	 * Dieser installiert nie selbstständig ein Update.
	 */
	public static function render_update_notice(): void {
		global $pagenow;

		if ( ! in_array( $pagenow, array( 'plugins.php', 'update-core.php' ), true ) || ! current_user_can( 'update_plugins' ) ) {
			return;
		}
		?>
		<div class="notice notice-info">
			<p><strong><?php esc_html_e( 'MGD Plugin-Updates', 'mgd-wp-seo-tool' ); ?></strong></p>
			<p><?php esc_html_e( 'Die Prüfung fragt ausschließlich die festgelegte GitHub-Release-Quelle ab. Es wird dabei kein Update installiert.', 'mgd-wp-seo-tool' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'mgd_wp_seo_check_updates' ); ?>
				<input type="hidden" name="action" value="mgd_wp_seo_check_updates">
				<p><button class="button" type="submit"><?php esc_html_e( 'MGD-Updates jetzt prüfen', 'mgd-wp-seo-tool' ); ?></button></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Löscht ausschließlich den eigenen Release-Cache und stößt WordPress' normale
	 * Updateprüfung an. Nonce und capability schützen vor CSRF und Fremdzugriff.
	 */
	public static function check_updates(): void {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! current_user_can( 'update_plugins' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'mgd-wp-seo-tool' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( 'mgd_wp_seo_check_updates' );

		foreach ( self::$plugins as $plugin ) {
			delete_site_transient( self::get_cache_key( $plugin ) );
		}

		$state = get_site_transient( 'update_plugins' );
		if ( is_object( $state ) ) {
			$state->last_checked = 0;
			set_site_transient( 'update_plugins', $state );
		}

		wp_update_plugins();
		wp_safe_redirect( admin_url( 'update-core.php?mgd_wp_seo_checked=1' ) );
		exit;
	}

	/**
	 * Ergänzt nützliche Links direkt in der Plugin-Liste.
	 *
	 * @param string[] $links Bestehende WordPress-Links.
	 * @return string[]
	 */
	public static function add_plugin_links( array $links, string $file ): array {
		if ( ! isset( self::$plugins[ $file ] ) ) {
			return $links;
		}

		$plugin = self::$plugins[ $file ];
		$repo_url = self::get_repo_url( $plugin );
		$targets = array(
			esc_html__( 'Dokumentation', 'mgd-wp-seo-tool' ) => $repo_url . '/blob/main/README.md',
			esc_html__( 'GitHub', 'mgd-wp-seo-tool' )       => $repo_url,
		);

		foreach ( $targets as $label => $url ) {
			$links[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), esc_html( $label ) );
		}

		return $links;
	}

	/**
	 * Liefert eine informative, lokale WordPress-Detailansicht ohne Fremdcode.
	 *
	 * @param mixed  $result Bereits vorhandene Plugin-Informationen.
	 * @param string $action Angefragte WordPress-Aktion.
	 * @param object $args   WordPress-Argumente.
	 * @return mixed
	 */
	public static function plugin_information( $result, string $action, object $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		foreach ( self::$plugins as $plugin ) {
			if ( ( $args->slug ?? '' ) !== $plugin['slug'] ) {
				continue;
			}

			$info = is_object( $result ) && ! is_wp_error( $result ) ? $result : new stdClass();
			$info->name = $plugin['name'];
			$info->slug = $plugin['slug'];
			$info->version = $plugin['version'];
			$info->author = '<a href="https://michael-gahn.de/">Michael Gahn DESIGN</a>';
			$info->homepage = self::get_repo_url( $plugin );
			$info->requires = $plugin['requires'];
			$info->requires_php = $plugin['php'];
			$info->external = true;
			$info->sections = array(
				'description' => '<h2>' . esc_html( $plugin['name'] ) . '</h2><p>' . esc_html( $plugin['description'] ) . '</p>',
				'installation' => '<p>' . esc_html__( 'Vor einem Update eine geprüfte Sicherung erstellen. Anschließend das von WordPress angebotene Release installieren und die Tool-Seite testen.', 'mgd-wp-seo-tool' ) . '</p>',
				'faq' => '<p>' . esc_html__( 'Unter Dashboard → Aktualisierungen kann „MGD-Updates jetzt prüfen“ verwendet werden. Diese Aktion installiert kein Update.', 'mgd-wp-seo-tool' ) . '</p>',
			);

			return $info;
		}

		return $result;
	}

	/**
	 * Akzeptiert nur eine semantische, veröffentlichte GitHub-Version samt exakt
	 * benanntem ZIP-Asset. Bei jedem Fehler wird kein Update angeboten.
	 *
	 * @param mixed                $update Bisheriges Update-Angebot.
	 * @param array<string, mixed> $data   WordPress-Plugin-Daten.
	 * @param string               $file   Plugin-Dateipfad relativ zu wp-content/plugins.
	 * @return array<string, string>|false|mixed
	 */
	public static function offer_update( $update, array $data, string $file ) {
		$plugin = self::$plugins[ $file ] ?? null;
		if ( ! is_array( $plugin ) || empty( $plugin['package'] ) ) {
			return $update;
		}

		$release = get_site_transient( self::get_cache_key( $plugin ) );
		if ( false === $release ) {
			$release = self::fetch_release( $plugin );
			set_site_transient( self::get_cache_key( $plugin ), $release, $release ? 6 * HOUR_IN_SECONDS : 15 * MINUTE_IN_SECONDS );
		}

		return is_array( $release ) ? $release : false;
	}

	/**
	 * Fragt die GitHub-API defensiv ab und validiert alle serverfremden Werte.
	 *
	 * @param array<string, string> $plugin Plugin-Metadaten.
	 * @return array<string, string>
	 */
	private static function fetch_release( array $plugin ): array {
		$response = wp_safe_remote_get(
			'https://api.github.com/repos/MichaelGahnDESIGN/' . rawurlencode( $plugin['repo'] ) . '/releases/latest',
			array(
				'timeout'             => 8,
				'redirection'         => 0,
				'limit_response_size' => 262144,
				'headers'             => array( 'Accept' => 'application/vnd.github+json' ),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || ! empty( $body['draft'] ) || ! empty( $body['prerelease'] ) || ! preg_match( '/^v?((?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*))$/D', (string) ( $body['tag_name'] ?? '' ), $matches ) ) {
			return array();
		}

		$version = $matches[1];
		$tag = (string) $body['tag_name'];
		$package_name = str_replace( '{version}', $version, $plugin['package'] );
		$package_url = self::get_repo_url( $plugin ) . '/releases/download/' . rawurlencode( $tag ) . '/' . rawurlencode( $package_name );

		foreach ( (array) ( $body['assets'] ?? array() ) as $asset ) {
			if ( is_array( $asset ) && ( $asset['name'] ?? '' ) === $package_name && ( $asset['browser_download_url'] ?? '' ) === $package_url ) {
				return array(
					'version' => $version,
					'package' => $package_url,
					'slug'    => $plugin['slug'],
				);
			}
		}

		return array();
	}

	/** @param array<string, string> $plugin */
	private static function get_cache_key( array $plugin ): string {
		return 'mgd_wp_seo_release_' . sanitize_key( $plugin['slug'] );
	}

	/** @param array<string, string> $plugin */
	private static function get_repo_url( array $plugin ): string {
		return 'https://github.com/MichaelGahnDESIGN/' . rawurlencode( $plugin['repo'] );
	}
}
