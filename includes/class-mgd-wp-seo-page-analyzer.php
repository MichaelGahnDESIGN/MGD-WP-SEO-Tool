<?php
/** Analysiert begrenztes, bereits abgerufenes HTML ohne weitere Netzwerkzugriffe. */
defined( 'ABSPATH' ) || exit;

final class MGD_WP_SEO_Page_Analyzer {

	/** Nur kurze Textwerte und Zähler verlassen den Parser; niemals fremdes HTML. */
	public static function analyze( string $html ): array {
		if ( ! class_exists( 'DOMDocument' ) ) {
			return array( 'available' => false );
		}
		$previous = libxml_use_internal_errors( true );
		$doc = new DOMDocument();
		$doc->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		$text = static fn( $value ) => trim( wp_html_excerpt( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $value ) ) ?: '', 320, '' ) );
		$data = array( 'available' => true, 'title' => '', 'description' => '', 'robots' => '', 'canonical' => '', 'lang' => '', 'viewport' => '', 'h1' => array(), 'headings' => array(), 'schema' => array(), 'schema_errors' => 0, 'images' => 0, 'missing_alt' => 0, 'empty_alt' => 0, 'links' => 0, 'empty_links' => 0 );
		foreach ( $doc->getElementsByTagName( 'title' ) as $node ) {
			$data['title'] = $text( $node->textContent );
			break;
		}
		foreach ( $doc->getElementsByTagName( 'meta' ) as $node ) {
			$name = strtolower( $node->getAttribute( 'name' ) );
			if ( in_array( $name, array( 'description', 'robots', 'googlebot', 'viewport' ), true ) ) {
				$key = 'googlebot' === $name ? 'robots' : $name;
				$data[ $key ] = $text( $data[ $key ] . ' ' . $node->getAttribute( 'content' ) );
			}
		}
		foreach ( $doc->getElementsByTagName( 'link' ) as $node ) {
			if ( in_array( 'canonical', preg_split( '/\s+/', strtolower( $node->getAttribute( 'rel' ) ) ), true ) ) {
				$data['canonical'] = $text( $node->getAttribute( 'href' ) );
			}
		}
		$data['lang'] = $text( $doc->documentElement->getAttribute( 'lang' ) );
		$xpath = new DOMXPath( $doc );
		foreach ( $xpath->query( '//h1|//h2|//h3|//h4|//h5|//h6' ) as $node ) {
			if ( count( $data['headings'] ) < 100 ) {
				$data['headings'][] = strtoupper( $node->nodeName ) . ': ' . $text( $node->textContent );
			}
			if ( 'h1' === $node->nodeName && count( $data['h1'] ) < 20 ) {
				$data['h1'][] = $text( $node->textContent );
			}
		}
		foreach ( $doc->getElementsByTagName( 'img' ) as $node ) {
			++$data['images'];
			if ( ! $node->hasAttribute( 'alt' ) ) { ++$data['missing_alt']; }
			elseif ( '' === trim( $node->getAttribute( 'alt' ) ) ) { ++$data['empty_alt']; }
		}
		foreach ( $doc->getElementsByTagName( 'a' ) as $node ) {
			if ( ! $node->hasAttribute( 'href' ) ) { continue; }
			++$data['links'];
			if ( '' === trim( $node->textContent . $node->getAttribute( 'aria-label' ) ) && 0 === $node->getElementsByTagName( 'img' )->length ) { ++$data['empty_links']; }
		}
		foreach ( $doc->getElementsByTagName( 'script' ) as $node ) {
			if ( 'application/ld+json' !== strtolower( trim( $node->getAttribute( 'type' ) ) ) ) { continue; }
			$json = json_decode( $node->textContent, true, 32 );
			if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $json ) ) { ++$data['schema_errors']; continue; }
			// Iterativ und begrenzt: beliebig verschachteltes JSON darf keine Rekursion auslösen.
			$queue = array( $json );
			for ( $i = 0; $i < count( $queue ) && $i < 500; ++$i ) {
				foreach ( $queue[ $i ] as $key => $value ) {
					if ( '@type' === $key ) {
						foreach ( (array) $value as $type ) {
							if ( is_string( $type ) && count( $data['schema'] ) < 50 ) { $data['schema'][] = $text( $type ); }
						}
					} elseif ( is_array( $value ) && count( $queue ) < 500 ) { $queue[] = $value; }
				}
			}
		}
		$data['schema'] = array_values( array_unique( $data['schema'] ) );
		return $data;
	}
}
