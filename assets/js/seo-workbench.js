/** Tab-Bedienung und sichere Textausgabe der HTML-Analyse. Keine fremden HTML-Fragmente. */
( () => {
	'use strict';
	document.addEventListener( 'DOMContentLoaded', () => document.querySelectorAll( '.mgd-seo-tool' ).forEach( root => {
		const tabs = [ ...root.querySelectorAll( '[data-tab]' ) ];
		const panels = [ ...root.querySelectorAll( '[data-panel]' ) ];
		const outputs = [ ...root.querySelectorAll( '[data-audit-output]' ) ];
		const buttons = [ ...root.querySelectorAll( '[data-action="analyze"]' ) ];
		const urlInput = root.querySelector( '[data-field="url"]' );
		let auditRequest = 0;
		root.querySelector( '[data-shared-url]' ).append( urlInput.closest( '.mgd-seo-tool__field' ) );
		root.querySelector( '[data-shared-url]' ).append( root.querySelector( '[data-status]' ) );
		root.querySelector( '[role="tablist"]' ).hidden = false;
		root.querySelector( '[data-social-slot]' ).append( root.querySelector( '[data-action="fetch-social"]' ) );
		root.querySelector( '[data-social-slot]' ).append( root.querySelector( '.mgd-seo-tool__preview-card--social' ) );
		root.querySelector( '[data-campaign-slot]' ).append( root.querySelector( '[data-utm-generator]' ) );

		function activate( tab ) {
			tabs.forEach( item => { item.setAttribute( 'aria-selected', String( item === tab ) ); item.tabIndex = item === tab ? 0 : -1; } );
			panels.forEach( panel => { panel.hidden = panel.dataset.panel !== tab.dataset.tab; } );
		}
		tabs.forEach( ( tab, index ) => {
			tab.addEventListener( 'click', () => activate( tab ) );
			tab.addEventListener( 'keydown', event => {
				let next;
				if ( event.key === 'ArrowRight' ) next = ( index + 1 ) % tabs.length;
				if ( event.key === 'ArrowLeft' ) next = ( index + tabs.length - 1 ) % tabs.length;
				if ( event.key === 'Home' ) next = 0;
				if ( event.key === 'End' ) next = tabs.length - 1;
				if ( next !== undefined ) { event.preventDefault(); activate( tabs[ next ] ); tabs[ next ].focus(); }
			} );
		} );
		function render( target, rows ) {
			const list = document.createElement( 'ul' );
			list.className = 'mgd-seo-tool__audit-list';
			rows.forEach( ( [ label, value ] ) => {
				const item = document.createElement( 'li' );
				const title = document.createElement( 'strong' );
				title.textContent = label + ': ';
				item.append( title, document.createTextNode( String( value ) ) );
				list.append( item );
			} );
			target.replaceChildren( list );
		}
		function showAudit( data, url ) {
			const robots = [ data.robots, data.header_robots ].filter( Boolean ).join( ', ' );
			outputs.forEach( target => {
				const kind = target.dataset.auditOutput;
				let rows = [ [ 'Geprüfte URL', url ] ];
				if ( kind === 'audit' ) rows.push(
					[ 'HTTP-Status', data.status ], [ 'Seitentitel', data.title || 'Fehlt – einen eindeutigen Titel ergänzen.' ],
					[ 'Meta-Beschreibung', data.description || 'Fehlt – den Nutzen der Seite kurz beschreiben.' ],
					[ 'H1', data.h1.join( ' / ' ) || 'Keine H1 im HTML gefunden.' ],
					[ 'Sprache', data.lang || 'Kein lang-Attribut gefunden.' ],
					[ 'Mobile Viewport', data.viewport || 'Fehlt – mobile Darstellung prüfen.' ],
					[ 'Hinweis', 'Eine HTML-Prüfung belegt weder Rankings noch tatsächliche Google-Indexierung.' ] );
				if ( kind === 'schema' ) rows.push(
					[ 'JSON-LD-Typen', data.schema.join( ', ' ) || 'Keine Typen erkannt.' ],
					[ 'Nicht lesbare JSON-LD-Blöcke', data.schema_errors ],
					[ 'Überschriften', data.headings.join( '\n' ) || 'Keine Überschriften gefunden.' ],
					[ 'Validierung', 'Typ-Erkennung ist keine Schema- oder Rich-Result-Validierung. Für Googles Prüfung: search.google.com/test/rich-results' ] );
				if ( kind === 'media' ) rows.push(
					[ 'Bilder', data.images ], [ 'Ohne alt-Attribut', data.missing_alt ],
					[ 'Leerer Alt-Text', data.empty_alt + ' – bei dekorativen Bildern korrekt, sonst prüfen.' ],
					[ 'HTML-Links mit href', data.links ], [ 'Links ohne Text oder Bild', data.empty_links ],
					[ 'Prüfumfang', 'Nur Elemente im gelieferten HTML. Linkziele werden nicht abgerufen; Bildinhalt und Alt-Text-Qualität manuell prüfen.' ] );
				if ( kind === 'index' ) rows.push(
					[ 'Robots (Meta und HTTP)', robots || 'Keine Anweisung gefunden; kein Beleg für Indexierung.' ],
					[ 'Indexierungshinweis', /\bnoindex\b|\bnone\b/i.test( robots ) ? 'noindex/none erkannt: Eine Anweisung schließt die Seite aus Suchergebnissen aus.' : 'Kein noindex im geprüften HTML/HTTP-Header erkannt.' ],
					[ 'Canonical', data.canonical || 'Kein Canonical gefunden.' ],
					[ 'Prüfgrenze', 'robots.txt, Sitemap, Googlebot-spezifische Header und Googles gewählter Canonical wurden nicht geprüft.' ] );
				render( target, rows );
			} );
		}
		async function analyze() {
			const url = urlInput.value.trim();
			const request = ++auditRequest;
			let parsed;
			try { parsed = new URL( url ); } catch { outputs.forEach( el => { el.textContent = 'Bitte oben eine gültige HTTPS-URL eintragen.'; } ); urlInput.focus(); return; }
			if ( parsed.protocol !== 'https:' || parsed.username || parsed.password || ( parsed.port && parsed.port !== '443' ) ) {
				outputs.forEach( el => { el.textContent = 'Nur öffentliche HTTPS-URLs ohne Zugangsdaten oder Sonderport sind erlaubt.'; } ); return;
			}
			buttons.forEach( button => { button.disabled = true; } );
			outputs.forEach( el => { el.textContent = 'HTML wird abgerufen und geprüft …'; el.setAttribute( 'aria-busy', 'true' ); } );
			try {
				const config = window.mgdSeoToolConfig || {};
				const response = await fetch( config.ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' }, body: new URLSearchParams( { action: 'mgd_wp_seo_fetch_social_preview', nonce: config.socialNonce, url } ) } );
				const result = await response.json();
				if ( request !== auditRequest ) return;
				if ( ! response.ok || ! result.success || ! result.data?.audit?.available ) throw new Error( result.data?.message || 'Die HTML-Analyse ist auf diesem Server nicht verfügbar.' );
				if ( url !== urlInput.value.trim() ) throw new Error( 'Die URL wurde inzwischen geändert. Bitte erneut analysieren.' );
				showAudit( result.data.audit, url );
			} catch ( error ) { if ( request === auditRequest ) outputs.forEach( el => { el.textContent = error.message || 'Analyse fehlgeschlagen.'; } ); }
			finally { if ( request === auditRequest ) { buttons.forEach( button => { button.disabled = false; } ); outputs.forEach( el => el.removeAttribute( 'aria-busy' ) ); } }
		}
		buttons.forEach( button => button.addEventListener( 'click', analyze ) );
		function invalidateAudit( message ) { ++auditRequest; buttons.forEach( button => { button.disabled = false; } ); outputs.forEach( el => { el.removeAttribute( 'aria-busy' ); el.textContent = message; } ); }
		urlInput.addEventListener( 'input', () => invalidateAudit( 'URL geändert. Bitte erneut analysieren.' ) );
		root.addEventListener( 'mgd-seo-reset', () => invalidateAudit( 'Eingaben zurückgesetzt. Starte eine neue Analyse.' ) );
	} ) );
} )();
