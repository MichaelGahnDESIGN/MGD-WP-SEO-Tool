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

		checks.push( titleLength >= 50 && titleLength <= 60
			? [ 'good', 'Meta-Titel: gute Länge als Richtwert.' ]
			: [ 'hint', 'Meta-Titel: für die Darstellung sind etwa 50–60 Zeichen oft günstig.' ] );
		checks.push( descriptionLength >= 140 && descriptionLength <= 160
			? [ 'good', 'Meta-Beschreibung: gute Länge als Richtwert.' ]
			: [ 'hint', 'Meta-Beschreibung: etwa 140–160 Zeichen nutzen den verfügbaren Platz meist gut.' ] );

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
			const values = fields();
			output.titleCount.textContent = String( values.title.length );
			output.descriptionCount.textContent = String( values.description.length );
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
