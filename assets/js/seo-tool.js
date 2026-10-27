/**
 * Interaktive, vollständig lokale Bedienlogik des MGD WP-SEO-Tools.
 *
 * Es gibt bewusst keine Netzwerkzugriffe. Entwürfe werden nur nach einem
 * ausdrücklichen Klick im localStorage des aktuellen Browsers gespeichert.
 */
( () => {
	'use strict';

	const STORAGE_PREFIX = 'mgd-wp-seo-tool:';
	const FALLBACK = {
		title: 'Dein Seitentitel erscheint hier',
		description: 'Deine Meta-Beschreibung erscheint hier als kurze Zusammenfassung der Seite.',
		socialDescription: 'Die Beschreibung wird für eine Vorschau in sozialen Netzwerken übernommen.',
	};

	/** Gibt eine nutzbare Domain zurück, ohne unsicheren HTML-Inhalt einzufügen. */
	function getDomain( url ) {
		try {
			return new URL( url ).hostname.replace( /^www\./, '' ) || url;
		} catch ( error ) {
			return url.replace( /^https?:\/\//, '' ).split( '/' )[ 0 ] || 'deine-domain.de';
		}
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
			checks: root.querySelector( '[data-checks]' ),
			status: root.querySelector( '[data-status]' ),
		};

		function fields() {
			return Object.fromEntries( Object.entries( inputs ).map( ( [ name, input ] ) => [ name, input.value.trim() ] ) );
		}

		function setStatus( message ) {
			output.status.textContent = message;
		}

		function update() {
			const values = fields();
			output.titleCount.textContent = String( values.title.length );
			output.descriptionCount.textContent = String( values.description.length );
			output.url.textContent = values.url || root.dataset.defaultUrl;
			output.title.textContent = values.title || FALLBACK.title;
			output.description.textContent = values.description || FALLBACK.description;
			output.domain.textContent = getDomain( values.url || root.dataset.defaultUrl );
			output.socialTitle.textContent = values.title || FALLBACK.title;
			output.socialDescription.textContent = values.description || FALLBACK.socialDescription;
			output.checks.replaceChildren( ...makeChecks( values ).map( ( [ state, message ] ) => {
				const item = document.createElement( 'li' );
				item.className = `mgd-seo-tool__check mgd-seo-tool__check--${ state }`;
				item.textContent = message;
				return item;
			} ) );
		}

		async function copyMetadata() {
			const values = fields();
			const plainText = `Meta-Titel: ${ values.title }\nMeta-Beschreibung: ${ values.description }\nURL: ${ values.url }`;
			try {
				await navigator.clipboard.writeText( plainText );
				setStatus( 'Meta-Daten wurden in die Zwischenablage kopiert.' );
			} catch ( error ) {
				setStatus( 'Kopieren war in diesem Browser nicht möglich. Bitte Text manuell markieren.' );
			}
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
			Object.values( inputs ).forEach( ( input ) => { input.value = input.dataset.field === 'url' ? root.dataset.defaultUrl : ''; } );
			try {
				window.localStorage.removeItem( key );
			} catch ( error ) {
				// Auch bei gesperrtem localStorage bleibt das Zurücksetzen der Felder möglich.
			}
			update();
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

		Object.values( inputs ).forEach( ( input ) => input.addEventListener( 'input', update ) );
		root.querySelector( '[data-action="copy"]' ).addEventListener( 'click', copyMetadata );
		root.querySelector( '[data-action="save"]' ).addEventListener( 'click', saveDraft );
		root.querySelector( '[data-action="reset"]' ).addEventListener( 'click', reset );
		update();
	}

	document.addEventListener( 'DOMContentLoaded', () => document.querySelectorAll( '.mgd-seo-tool' ).forEach( initializeTool ) );
} )();
