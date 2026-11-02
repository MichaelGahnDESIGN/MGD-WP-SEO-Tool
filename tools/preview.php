<?php
/** Lokale UI-Vorschau mit WordPress-Ausgabehilfen; ausschließlich Entwicklungszwecke. */
define( 'ABSPATH', __DIR__ );
function esc_attr( $v ) { return htmlspecialchars( $v, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $v ) { return esc_attr( $v ); }
function esc_html_e( $v, $domain = '' ) { echo esc_html( $v ); }
function esc_attr_e( $v, $domain = '' ) { echo esc_attr( $v ); }
function wp_parse_url( $v, $part ) { return parse_url( $v, $part ); }
$instance_id = 'mgd-preview';
$default_url = 'https://example.de/';
?><!doctype html><html lang="de"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MGD SEO Werkzeuge – lokale Vorschau</title><link rel="stylesheet" href="../assets/css/seo-tool.css"><body style="margin:0;padding:1rem;background:#eef0f3"><?php require dirname( __DIR__ ) . '/templates/seo-tool.php'; ?><script src="../assets/js/seo-tool.js"></script><script src="../assets/js/seo-workbench.js"></script><script src="../assets/js/seo-generators.js"></script></body></html>
