/**
 * Interaktive Bedienlogik des MGD WP-SEO-Tools.
 *
 * Entwürfe bleiben lokal. Nur die Aktion „Socialdaten laden“ sendet die
 * eingegebene HTTPS-URL an die eigene WordPress-Installation, damit diese
 * ausgewählte Open-Graph-Angaben sicher abrufen kann.
 */
( () => {
	'use strict';

	const STORAGE_PREFIX = 'mgd-wp-seo-tool:';
	const SOCIAL_CONFIG = window.mgdSeoToolConfig || {};
	const FALLBACK = {
		title: 'Dein Seitentitel erscheint hier',
		description: 'Deine Meta-Beschreibung erscheint hier als kurze Zusammenfassung der Seite.',
		socialDescription: 'Die Beschreibung wird für eine Vorschau in sozialen Netzwerken übernommen.',
	};

	/** Gibt eine nutzbare Domain zurück, ohne fremden HTML-Inhalt einzufügen. */
	function getDomain( url ) {
		try {
			return new URL( url ).hostname.replace( /^www\./, '' ) || url;
		} catch ( error ) {
			return url.replace( /^https?:\/\//, '' ).split( '/' )[ 0 ] || 'deine-domain.de';
		}
	}

	/** Prüft die lokale URL vor dem bewussten Social-Abruf. */
	function isPublicHttpsUrl( value ) {
		try {
			const url = new URL( value );
			return url.protocol === 'https:' && ! url.username && ! url.password && ( ! url.port || url.port === '443' );
		} catch ( error ) {
			return false;
		}
	}

	/** Erzeugt HTML-Meta-Tags mit escaped Attributwerten für die Zwischenablage. */
	function escapeAttribute( value ) {
		return value.replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
	}

	/** Prüft Textlängen als Orientierung, ohne ein Google-Ranking zu versprechen. */
	function makeChecks( fields ) {
		const checks = [];
		const titleLength = fields.title.length;
		const descriptionLength = fields.description.length;
		const keyword = fields.keyword.trim().toLocaleLowerCase();

		checks.push( ! titleLength ? [ 'hint', 'Trage einen aussagekräftigen Seitentitel ein.' ] : titleLength > 60 ? [ 'hint', 'Der Titel überschreitet den Richtwert und könnte gekürzt werden.' ] : [ 'good', 'Der Titel liegt innerhalb des Zeichen-Richtwerts. Die tatsächliche Breite kann abweichen.' ] );
		checks.push( ! descriptionLength ? [ 'hint', 'Beschreibe kurz, was Besucher auf der Seite erwartet.' ] : descriptionLength > 160 ? [ 'hint', 'Die Beschreibung überschreitet den Richtwert und könnte gekürzt werden.' ] : [ 'good', 'Die Beschreibung liegt innerhalb des Zeichen-Richtwerts. Google kann andere Texte anzeigen.' ] );

		if ( keyword ) {
			const inTitle = fields.title.toLocaleLowerCase().includes( keyword );
			const inDescription = fields.description.toLocaleLowerCase().includes( keyword );
			checks.push( inTitle || inDescription
				? [ 'good', 'Fokus-Keyword kommt natürlich im Titel oder in der Beschreibung vor.' ]
				: [ 'hint', 'Fokus-Keyword fehlt bisher. Nur ergänzen, wenn es inhaltlich passt.' ] );
		}

		return checks;
	}

	function initializeTool( root ) {
		const key = STORAGE_PREFIX + root.id;
		const inputs = Object.fromEntries( [ ...root.querySelectorAll( '[data-field]' ) ].map( ( input ) => [ input.dataset.field, input ] ) );
		const output = {
			titleCount: root.querySelector( '[data-title-count]' ),
			descriptionCount: root.querySelector( '[data-description-count]' ),
			url: root.querySelector( '[data-preview-url]' ),
			title: root.querySelector( '[data-preview-title]' ),
			description: root.querySelector( '[data-preview-description]' ),
			domain: root.querySelector( '[data-social-domain]' ),
			socialTitle: root.querySelector( '[data-social-title]' ),
			socialDescription: root.querySelector( '[data-social-description]' ),
			socialImage: root.querySelector( '[data-social-image]' ),
			socialPlaceholder: root.querySelector( '[data-social-placeholder]' ),
			applySocial: root.querySelector( '[data-action="apply-social"]' ),
			fetchSocial: root.querySelector( '[data-action="fetch-social"]' ),
			checks: root.querySelector( '[data-checks]' ),
			status: root.querySelector( '[data-status]' ),
		};
		let socialData = null;
		let socialRequest = 0;
		const dialog = root.querySelector( '[data-info-dialog]' );
		const info = {
			title: [ 'Meta-Titel', 'Der Titel beschreibt den Inhalt einer Seite. Formuliere ihn einzigartig, konkret und gut lesbar. Wichtige Begriffe gehören nach vorn, wenn sie natürlich passen. Ein kurzer Markenname kann helfen. Vermeide Wiederholungen und Keyword-Listen.', '60 Zeichen sind ein praktischer Richtwert, keine Google-Grenze. Je nach Zeichenbreite und Gerät kann die Anzeige früher oder später enden. Google kann einen anderen Titel auswählen.', 'https://developers.google.com/search/docs/appearance/title-link?hl=de' ],
			description: [ 'Meta-Beschreibung', 'Fasse für jede Seite einzigartig zusammen, was Besucher erwartet und welchen Nutzen sie haben. Schreibe verständlich und passend zum tatsächlichen Inhalt. Vermeide allgemeine Werbephrasen und reine Keyword-Listen.', '160 Zeichen sind ein Richtwert, keine feste Google-Grenze. Google kürzt nach verfügbarer Breite und kann stattdessen einen passenden Ausschnitt aus dem Seiteninhalt verwenden.', 'https://developers.google.com/search/docs/appearance/snippet?hl=de' ],
			keyword: [ 'Fokus-Keyword', 'Das Fokus-Keyword ist eine Arbeitshilfe: Welche Suchanfrage soll die Seite beantworten? Verwende passende Begriffe natürlich in Titel, Überschrift und Inhalt. Wiederhole sie nicht künstlich.', 'Das Feld dient nur der lokalen Prüfung und wird nicht als Meta-Keywords-Tag veröffentlicht. Google verwendet den Meta-Keywords-Tag nicht für die Websuche.', 'https://developers.google.com/search/docs/fundamentals/seo-starter-guide?hl=de' ],
		};
		let infoTrigger = null;
		root.querySelectorAll( '[data-info-open]' ).forEach( button => button.addEventListener( 'click', () => {
			const [ title, tip, caveat, link ] = info[ button.dataset.infoOpen ];
			infoTrigger = button;
			root.querySelector( '[data-info-title]' ).textContent = title;
			const body = root.querySelector( '[data-info-body]' );
			const first = document.createElement( 'p' ); first.textContent = tip;
			const second = document.createElement( 'p' ); second.textContent = caveat;
			const source = document.createElement( 'a' ); source.href = link; source.target = '_blank'; source.rel = 'noopener noreferrer'; source.textContent = 'Google-Dokumentation öffnen';
			body.replaceChildren( first, second, source );
			if ( typeof dialog.showModal === 'function' ) dialog.showModal(); else dialog.setAttribute( 'open', '' );
			root.querySelector( '[data-info-close]' ).focus();
		} ) );
		root.querySelectorAll( '[data-info-close]' ).forEach( button => button.addEventListener( 'click', () => { if ( typeof dialog.close === 'function' ) dialog.close(); else dialog.removeAttribute( 'open' ); } ) );
		dialog.addEventListener( 'close', () => { if ( infoTrigger ) infoTrigger.focus(); } );

		function fields() {
			return Object.fromEntries( Object.entries( inputs ).map( ( [ name, input ] ) => [ name, input.value.trim() ] ) );
		}

		function setStatus( message ) {
			output.status.textContent = message;
		}

		function clearSocialImage() {
			output.socialImage.removeAttribute( 'src' );
			output.socialImage.alt = '';
			output.socialImage.hidden = true;
			output.socialPlaceholder.hidden = false;
		}

		function update() {
			// Auch übernommene OG-Daten und programmatisch gesetzte Werte respektieren das Eingabelimit.
			[ inputs.title, inputs.description ].forEach( input => { if ( input.maxLength >= 0 && input.value.length > input.maxLength ) input.value = input.value.slice( 0, input.maxLength ); } );
			const values = fields();
			output.titleCount.textContent = String( inputs.title.value.length );
			output.descriptionCount.textContent = String( inputs.description.value.length );
			inputs.title.classList.toggle( 'mgd-seo-tool__input--over', inputs.title.value.length > 60 );
			inputs.description.classList.toggle( 'mgd-seo-tool__input--over', inputs.description.value.length > 160 );
			output.url.textContent = values.url || root.dataset.defaultUrl;
			output.title.textContent = values.title || FALLBACK.title;
			output.description.textContent = values.description || FALLBACK.description;
			output.domain.textContent = getDomain( values.url || root.dataset.defaultUrl );
			output.socialTitle.textContent = socialData?.title || values.title || FALLBACK.title;
			output.socialDescription.textContent = socialData?.description || values.description || FALLBACK.socialDescription;
			output.checks.replaceChildren( ...makeChecks( values ).map( ( [ state, message ] ) => {
				const item = document.createElement( 'li' );
				item.className = `mgd-seo-tool__check mgd-seo-tool__check--${ state }`;
				item.textContent = message;
				return item;
			} ) );
		}

		async function copyText( text, successMessage ) {
			try {
				await navigator.clipboard.writeText( text );
				setStatus( successMessage );
			} catch ( error ) {
				setStatus( 'Kopieren war in diesem Browser nicht möglich. Bitte den Text manuell markieren.' );
			}
		}

		function copyMetadata() {
			const values = fields();
			return copyText( `Meta-Titel: ${ values.title }\nMeta-Beschreibung: ${ values.description }\nURL: ${ values.url }`, 'Meta-Daten wurden in die Zwischenablage kopiert.' );
		}

		function copyTags() {
			const values = fields();
			const lines = [
				`<title>${ escapeAttribute( values.title ) }</title>`,
				`<meta name="description" content="${ escapeAttribute( values.description ) }">`,
				`<meta property="og:title" content="${ escapeAttribute( socialData?.title || values.title ) }">`,
				`<meta property="og:description" content="${ escapeAttribute( socialData?.description || values.description ) }">`,
				`<meta property="og:url" content="${ escapeAttribute( values.url ) }">`,
			];
			if ( socialData?.image ) lines.push( `<meta property="og:image" content="${ escapeAttribute( socialData.image ) }">` );

			return copyText( lines.join( '\n' ), 'Meta-Tags wurden in die Zwischenablage kopiert.' );
		}

		async function fetchSocialData() {
			const values = fields();
			const request = ++socialRequest;
			if ( ! isPublicHttpsUrl( values.url ) ) {
				setStatus( 'Bitte gib eine öffentliche HTTPS-URL ohne Anmeldung oder Sonderport ein.' );
				inputs.url.focus();
				return;
			}
			if ( ! SOCIAL_CONFIG.ajaxUrl || ! SOCIAL_CONFIG.socialNonce ) {
				setStatus( 'Die Social-Vorschau ist momentan nicht verfügbar. Bitte lade die Seite neu.' );
				return;
			}

			output.fetchSocial.disabled = true;
			output.fetchSocial.setAttribute( 'aria-busy', 'true' );
			setStatus( 'Socialdaten werden geladen …' );

			try {
				const response = await window.fetch( SOCIAL_CONFIG.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
					body: new URLSearchParams( { action: 'mgd_wp_seo_fetch_social_preview', nonce: SOCIAL_CONFIG.socialNonce, url: values.url } ),
				} );
				const result = await response.json();
				if ( request !== socialRequest || values.url !== fields().url ) return;
				if ( ! response.ok || ! result.success || ! result.data ) throw new Error( result?.data?.message || 'Socialdaten konnten nicht geladen werden.' );

				socialData = {
					title: typeof result.data.title === 'string' ? result.data.title : '',
					description: typeof result.data.description === 'string' ? result.data.description : '',
					image: typeof result.data.image === 'string' ? result.data.image : '',
				};
				if ( socialData.image ) {
					output.socialImage.src = socialData.image;
					output.socialImage.alt = `Social-Vorschaubild von ${ getDomain( values.url ) }`;
					output.socialImage.hidden = false;
					output.socialPlaceholder.hidden = true;
				} else {
					clearSocialImage();
				}
				output.applySocial.disabled = ! socialData.title && ! socialData.description;
				update();
				setStatus( socialData.image ? 'Socialdaten inklusive OG-Bild wurden geladen.' : 'Socialdaten wurden geladen. Die Zielseite liefert kein öffentliches OG-Bild.' );
			} catch ( error ) {
				if ( request !== socialRequest ) return;
				socialData = null;
				clearSocialImage();
				output.applySocial.disabled = true;
				update();
				setStatus( error instanceof Error ? error.message : 'Socialdaten konnten nicht geladen werden.' );
			} finally {
				if ( request === socialRequest ) {
					output.fetchSocial.disabled = false;
					output.fetchSocial.removeAttribute( 'aria-busy' );
				}
			}
		}

		function applySocialData() {
			if ( ! socialData ) return;
			if ( socialData.title ) inputs.title.value = socialData.title;
			if ( socialData.description ) inputs.description.value = socialData.description;
			update();
			setStatus( 'OG-Titel und OG-Beschreibung wurden in den Entwurf übernommen.' );
		}

		function saveDraft() {
			try {
				window.localStorage.setItem( key, JSON.stringify( fields() ) );
				setStatus( 'Entwurf wurde nur in diesem Browser gespeichert.' );
			} catch ( error ) {
				setStatus( 'Lokales Speichern ist in diesem Browser nicht verfügbar.' );
			}
		}

		function reset() {
			++socialRequest;
			output.fetchSocial.disabled = false;
			output.fetchSocial.removeAttribute( 'aria-busy' );
			Object.values( inputs ).forEach( ( input ) => { input.value = input.dataset.field === 'url' ? root.dataset.defaultUrl : ''; } );
			socialData = null;
			clearSocialImage();
			output.applySocial.disabled = true;
			try {
				window.localStorage.removeItem( key );
			} catch ( error ) {
				// Auch bei gesperrtem localStorage bleibt das Zurücksetzen der Felder möglich.
			}
			update();
			root.dispatchEvent( new CustomEvent( 'mgd-seo-reset' ) );
			setStatus( 'Eingaben und lokaler Entwurf wurden entfernt.' );
		}

		try {
			const saved = JSON.parse( window.localStorage.getItem( key ) || 'null' );
			if ( saved && typeof saved === 'object' ) {
				Object.entries( saved ).forEach( ( [ name, value ] ) => {
					if ( inputs[ name ] && typeof value === 'string' ) inputs[ name ].value = value;
				} );
				setStatus( 'Dein lokal gespeicherter Entwurf wurde geladen.' );
			}
		} catch ( error ) {
			// Eine defekte lokale Speicherung darf das Tool nicht blockieren.
		}

		Object.values( inputs ).forEach( ( input ) => input.addEventListener( 'input', () => {
			if ( input.dataset.field === 'url' ) {
				++socialRequest;
				output.fetchSocial.disabled = false;
				output.fetchSocial.removeAttribute( 'aria-busy' );
				socialData = null;
				clearSocialImage();
				output.applySocial.disabled = true;
			}
			update();
		} ) );
		output.socialImage.addEventListener( 'error', () => {
			clearSocialImage();
			setStatus( 'Das OG-Bild konnte im Browser nicht dargestellt werden.' );
		} );
		root.querySelector( '[data-action="copy"]' ).addEventListener( 'click', copyMetadata );
		root.querySelector( '[data-action="copy-tags"]' ).addEventListener( 'click', copyTags );
		output.fetchSocial.addEventListener( 'click', fetchSocialData );
		output.applySocial.addEventListener( 'click', applySocialData );
		root.querySelector( '[data-action="save"]' ).addEventListener( 'click', saveDraft );
		root.querySelector( '[data-action="reset"]' ).addEventListener( 'click', reset );
		clearSocialImage();
		update();
	}

	document.addEventListener( 'DOMContentLoaded', () => document.querySelectorAll( '.mgd-seo-tool' ).forEach( initializeTool ) );
} )();
