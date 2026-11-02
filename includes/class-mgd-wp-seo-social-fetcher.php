<?php
/**
 * Geschützter Abruf von Open-Graph-Daten für die Social-Vorschau.
 *
 * Der Abruf ist bewusst kein allgemeiner Web-Proxy: Er akzeptiert nur öffentliche
 * HTTPS-Ziele, folgt keinen Weiterleitungen und gibt ausschließlich kurze,
 * bereinigte Metadaten zurück. So kann die öffentliche Tool-Seite kein internes
 * Netz der Website abfragen (SSRF-Schutz).
 */

defined( 'ABSPATH' ) || exit;

final class MGD_WP_SEO_Social_Fetcher {

	/** @var self|null Singleton-Instanz. */
	private static ?self $instance = null;

	/** Maximale Größe einer abgerufenen HTML-Antwort in Bytes. */
	private const MAX_RESPONSE_BYTES = 262144;

	/** Gibt die einzige Instanz zurück und registriert die AJAX-Endpunkte. */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_mgd_wp_seo_fetch_social_preview', array( $this, 'handle_request' ) );
		add_action( 'wp_ajax_nopriv_mgd_wp_seo_fetch_social_preview', array( $this, 'handle_request' ) );
	}

	/**
	 * Liefert ausgewählte Open-Graph-Angaben als JSON.
	 *
	 * Die Nonce schützt vor fremden Formularen. Sie ersetzt keine Berechtigung,
	 * denn das Tool ist absichtlich öffentlich; daher begrenzen zusätzlich URL-
	 * Prüfung, SSRF-Schutz, kurze Timeouts, eine Antwortgrenze und ein flüchtiges
	 * Rate-Limit die Angriffsfläche.
	 */
	public function handle_request(): void {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'mgd_wp_seo_social_preview' ) ) {
			wp_send_json_error( array( 'message' => __( 'Die Anfrage ist abgelaufen. Bitte lade die Seite neu.', 'mgd-wp-seo-tool' ) ), 403 );
		}

		if ( ! $this->allow_request() ) {
			wp_send_json_error( array( 'message' => __( 'Zu viele Abrufe in kurzer Zeit. Bitte versuche es in einer Minute erneut.', 'mgd-wp-seo-tool' ) ), 429 );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( trim( (string) wp_unslash( $_POST['url'] ) ) ) : '';
		if ( ! $this->is_allowed_url( $url ) ) {
			wp_send_json_error( array( 'message' => __( 'Bitte gib eine öffentliche HTTPS-URL ohne Anmeldung oder Sonderport ein.', 'mgd-wp-seo-tool' ) ), 400 );
		}

		$cache_key = 'mgd_wp_seo_v2_' . sha1( $url );
		$metadata = get_transient( $cache_key );
		if ( ! is_array( $metadata ) ) {
			$response = wp_safe_remote_get(
				$url,
				array(
					'timeout'             => 6,
					'redirection'         => 0,
					'reject_unsafe_urls'  => true,
					'limit_response_size' => self::MAX_RESPONSE_BYTES,
					'headers'             => array( 'Accept' => 'text/html,application/xhtml+xml' ),
				)
			);

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				wp_send_json_error( array( 'message' => __( 'Die Socialdaten konnten nicht geladen werden. Prüfe bitte die URL.', 'mgd-wp-seo-tool' ) ), 422 );
			}

			$content_type = strtolower( (string) wp_remote_retrieve_header( $response, 'content-type' ) );
			if ( ! str_starts_with( $content_type, 'text/html' ) && ! str_starts_with( $content_type, 'application/xhtml+xml' ) ) {
				wp_send_json_error( array( 'message' => __( 'Die URL liefert keine HTML-Seite für eine Social-Vorschau.', 'mgd-wp-seo-tool' ) ), 422 );
			}

			$body = wp_remote_retrieve_body( $response );
			if ( strlen( $body ) >= self::MAX_RESPONSE_BYTES ) {
				wp_send_json_error( array( 'message' => 'Die Seite überschreitet die Analysegrenze von 256 KiB. Es werden keine unvollständigen Ergebnisse ausgegeben.' ), 422 );
			}
			$metadata = $this->extract_metadata( $body );
			$metadata['audit'] = MGD_WP_SEO_Page_Analyzer::analyze( $body );
			$metadata['audit']['status'] = 200;
			$metadata['audit']['header_robots'] = $this->clean_text( (string) wp_remote_retrieve_header( $response, 'x-robots-tag' ), 320 );
			set_transient( $cache_key, $metadata, 12 * HOUR_IN_SECONDS );
		}

		$metadata['domain'] = (string) wp_parse_url( $url, PHP_URL_HOST );
		wp_send_json_success( $metadata );
	}

	/**
	 * Erlaubt höchstens zwölf Abrufe pro Minute und Besucher. Es wird nur ein
	 * mit WordPress-Salz abgeleiteter, flüchtiger Schlüssel gespeichert, nie die IP.
	 */
	private function allow_request(): bool {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
		$key = 'mgd_wp_seo_rate_' . hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) );
		$count = (int) get_transient( $key );
		if ( $count >= 12 ) {
			return false;
		}

		set_transient( $key, $count + 1, MINUTE_IN_SECONDS );
		return true;
	}

	/**
	 * Akzeptiert nur öffentliche HTTPS-Hosts. Direkte IP-Adressen, lokale Namen,
	 * private/reservierte DNS-Ergebnisse, Login-URLs und Sonderports sind tabu.
	 */
	private function is_allowed_url( string $url ): bool {
		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || 'https' !== strtolower( (string) ( $parts['scheme'] ?? '' ) ) || empty( $parts['host'] ) || isset( $parts['user'] ) || isset( $parts['pass'] ) ) {
			return false;
		}

		if ( isset( $parts['port'] ) && 443 !== (int) $parts['port'] ) {
			return false;
		}

		$host = strtolower( rtrim( (string) $parts['host'], '.' ) );
		if ( '' === $host || 'localhost' === $host || str_ends_with( $host, '.local' ) || str_ends_with( $host, '.internal' ) || filter_var( $host, FILTER_VALIDATE_IP ) || ! wp_http_validate_url( $url ) ) {
			return false;
		}

		return $this->has_only_public_dns_records( $host );
	}

	/** Prüft A- und AAAA-DNS-Ergebnisse, bevor WordPress die Zielseite abruft. */
	private function has_only_public_dns_records( string $host ): bool {
		$addresses = array();
		if ( function_exists( 'dns_get_record' ) ) {
			$records = dns_get_record( $host, DNS_A + DNS_AAAA );
			foreach ( (array) $records as $record ) {
				if ( isset( $record['ip'] ) ) {
					$addresses[] = (string) $record['ip'];
				}
				if ( isset( $record['ipv6'] ) ) {
					$addresses[] = (string) $record['ipv6'];
				}
			}
		} else {
			$addresses = (array) gethostbynamel( $host );
		}

		if ( empty( $addresses ) ) {
			return false;
		}

		foreach ( $addresses as $address ) {
			if ( ! filter_var( $address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Liest nur die für eine Vorschau nötigen Angaben aus vertrauensfremdem HTML.
	 * Das HTML wird niemals an den Browser weitergegeben oder als HTML gerendert.
	 *
	 * @return array{title: string, description: string, image: string}
	 */
	private function extract_metadata( string $html ): array {
		$metadata = array(
			'title'       => '',
			'description' => '',
			'image'       => '',
		);

		if ( ! class_exists( 'DOMDocument' ) || '' === trim( $html ) ) {
			return $metadata;
		}

		$previous = libxml_use_internal_errors( true );
		$document = new DOMDocument();
		$document->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		$standard_description = '';
		foreach ( $document->getElementsByTagName( 'meta' ) as $element ) {
			$key = strtolower( trim( $element->getAttribute( 'property' ) ?: $element->getAttribute( 'name' ) ) );
			$value = $this->clean_text( $element->getAttribute( 'content' ), 320 );

			if ( 'og:title' === $key && '' === $metadata['title'] ) {
				$metadata['title'] = $value;
			} elseif ( 'og:description' === $key && '' === $metadata['description'] ) {
				$metadata['description'] = $value;
			} elseif ( 'description' === $key && '' === $standard_description ) {
				$standard_description = $value;
			} elseif ( 'og:image' === $key && '' === $metadata['image'] && $this->is_allowed_url( $value ) ) {
				$metadata['image'] = $value;
			}
		}

		if ( '' === $metadata['description'] ) {
			$metadata['description'] = $standard_description;
		}

		if ( '' === $metadata['title'] ) {
			$titles = $document->getElementsByTagName( 'title' );
			if ( $titles->length > 0 ) {
				$metadata['title'] = $this->clean_text( $titles->item( 0 )->textContent, 160 );
			}
		}

		return $metadata;
	}

	/** Bereinigt fremden Text und begrenzt seine Länge vor der JSON-Ausgabe. */
	private function clean_text( string $value, int $limit ): string {
		$value = html_entity_decode( wp_strip_all_tags( $value ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$value = preg_replace( '/\s+/u', ' ', $value ) ?: '';

		return trim( wp_html_excerpt( $value, $limit, '' ) );
	}
}
