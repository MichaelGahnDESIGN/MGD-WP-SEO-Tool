/** Lokale Generatoren: Eingaben werden nicht übertragen oder automatisch veröffentlicht. */
( () => {
	'use strict';
	document.addEventListener( 'DOMContentLoaded', () => document.querySelectorAll( '.mgd-seo-tool' ).forEach( root => {
		const field = name => root.querySelector( `[data-schema="${ name }"]` ).value.trim();
		const type = root.querySelector( '[data-schema="type"]' );
		const result = root.querySelector( '[data-schema-result]' );
		const status = root.querySelector( '[data-schema-status]' );
		const validUrl = value => { try { const url = new URL( value ); return [ 'https:', 'http:' ].includes( url.protocol ) && ! url.username && ! url.password; } catch { return false; } };
		function updateFields() {
			root.querySelector( '[data-schema-address]' ).hidden = type.value !== 'LocalBusiness';
			root.querySelector( '[data-schema-article]' ).hidden = type.value !== 'Article';
		}
		type.addEventListener( 'change', updateFields );
		root.querySelector( '[data-schema-generator]' ).addEventListener( 'input', event => { if ( event.target.matches( '[data-schema]' ) ) { result.value = ''; status.textContent = 'Eingaben geändert. Bitte den Code erneut erstellen.'; } } );
		type.addEventListener( 'change', () => { result.value = ''; status.textContent = 'Typ geändert. Bitte den Code erneut erstellen.'; } );
		root.querySelector( '[data-schema-create]' ).addEventListener( 'click', () => {
			result.value = '';
			if ( ! field( 'name' ) || ! validUrl( field( 'url' ) ) || ( field( 'image' ) && ! validUrl( field( 'image' ) ) ) ) { status.textContent = 'Bitte Name und gültige Seiten-URL eintragen; eine Bild-URL muss ebenfalls gültig sein.'; return; }
			if ( type.value === 'LocalBusiness' && ( ! field( 'street' ) || ! field( 'city' ) || ! field( 'postal' ) || ! /^[a-z]{2}$/i.test( field( 'country' ) ) ) ) { status.textContent = 'Bitte eine vollständige Adresse und einen zweistelligen Ländercode eingeben.'; return; }
			const schema = { '@context': 'https://schema.org', '@type': type.value };
			if ( type.value === 'Article' ) {
				schema.headline = field( 'name' ); schema.mainEntityOfPage = field( 'url' );
				if ( field( 'author' ) ) schema.author = { '@type': 'Person', name: field( 'author' ) };
				if ( field( 'date' ) ) schema.datePublished = field( 'date' );
			} else { schema.name = field( 'name' ); schema.url = field( 'url' ); }
			if ( field( 'image' ) ) schema[ type.value === 'Organization' ? 'logo' : 'image' ] = field( 'image' );
			if ( type.value === 'LocalBusiness' ) schema.address = { '@type': 'PostalAddress', streetAddress: field( 'street' ), postalCode: field( 'postal' ), addressLocality: field( 'city' ), addressCountry: field( 'country' ).toUpperCase() };
			// Escaped HTML-Sonderzeichen verhindern ein vorzeitiges Schließen des Script-Tags.
			const json = JSON.stringify( schema, null, 2 ).replace( /[<>&]/g, char => ( { '<': '\\u003c', '>': '\\u003e', '&': '\\u0026' } )[ char ] );
			result.value = `<script type="application/ld+json">\n${ json }\n</script>`;
			status.textContent = 'Entwurf erstellt. Bitte Inhalt und bestehende Schema-Daten prüfen.';
		} );
		async function copy( input, message ) { if ( ! input.value ) { message.textContent = 'Bitte zuerst ein Ergebnis erstellen.'; return; } try { await navigator.clipboard.writeText( input.value ); message.textContent = 'In die Zwischenablage kopiert.'; } catch { input.focus(); input.select(); message.textContent = 'Bitte den markierten Text manuell kopieren.'; } }
		root.querySelector( '[data-schema-copy]' ).addEventListener( 'click', () => copy( result, status ) );
		const utm = name => root.querySelector( `[data-utm="${ name }"]` ).value.trim();
		const utmResult = root.querySelector( '[data-utm-result]' );
		const utmStatus = root.querySelector( '[data-utm-status]' );
		root.querySelector( '[data-utm-generator]' ).addEventListener( 'input', event => { if ( event.target.matches( '[data-utm]' ) ) { utmResult.value = ''; utmStatus.textContent = 'Eingaben geändert. Bitte den Link erneut erstellen.'; } } );
		root.querySelector( '[data-utm-create]' ).addEventListener( 'click', () => {
			utmResult.value = '';
			if ( ! validUrl( utm( 'url' ) ) || ! utm( 'source' ) || ! utm( 'medium' ) || ! utm( 'campaign' ) ) { utmStatus.textContent = 'Bitte gültige Ziel-URL, Quelle, Medium und Kampagne eingeben.'; return; }
			const url = new URL( utm( 'url' ) );
			[ 'source', 'medium', 'campaign' ].forEach( name => url.searchParams.set( `utm_${ name }`, utm( name ) ) );
			utmResult.value = url.href; utmStatus.textContent = 'Kampagnenlink erstellt.';
		} );
		root.querySelector( '[data-utm-copy]' ).addEventListener( 'click', () => copy( utmResult, utmStatus ) );
		updateFields();
	} ) );
} )();
