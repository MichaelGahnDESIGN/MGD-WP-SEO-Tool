<?php
/** Kleiner Funktionstest mit festem HTML, ohne Netzwerk oder WordPress-Datenbank. */
define( 'ABSPATH', __DIR__ );
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function wp_html_excerpt( $value, $limit, $suffix ) { return mb_substr( $value, 0, $limit ); }
require dirname( __DIR__ ) . '/includes/class-mgd-wp-seo-page-analyzer.php';
$data = MGD_WP_SEO_Page_Analyzer::analyze( '<html lang="de"><head><title>Grüße</title><meta name="robots" content="noindex"><link rel="canonical" href="https://example.de/"></head><body><h1>Hallo</h1><h2>Mehr</h2><img src="a"><img alt=""><a href="/">Kontakt</a><a href="/"></a><script type="application/ld+json">{"@graph":[{"@type":"Article"}]}</script><script type="application/ld+json">{broken}</script></body></html>' );
$expected = array( 'title' => 'Grüße', 'lang' => 'de', 'robots' => 'noindex', 'canonical' => 'https://example.de/', 'h1' => array( 'Hallo' ), 'schema' => array( 'Article' ), 'schema_errors' => 1, 'images' => 2, 'missing_alt' => 1, 'empty_alt' => 1, 'links' => 2, 'empty_links' => 1 );
foreach ( $expected as $key => $value ) {
	if ( $data[ $key ] !== $value ) { fwrite( STDERR, 'Fehler: ' . $key . PHP_EOL ); exit( 1 ); }
}
echo "HTML-Analyse: 12 Erwartungen erfüllt.\n";
