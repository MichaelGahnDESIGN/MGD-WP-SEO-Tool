<?php
/**
 * Sichtbare Oberfläche des Shortcodes [mgd_wp_seo_tool].
 *
 * Variablen $instance_id und $default_url werden ausschließlich aus der
 * kontrollierten Plugin-Klasse übergeben und beim Ausgeben escaped.
 *
 * @var string $instance_id
 * @var string $default_url
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="mgd-seo-tool" id="<?php echo esc_attr( $instance_id ); ?>" data-default-url="<?php echo esc_attr( $default_url ); ?>" aria-labelledby="<?php echo esc_attr( $instance_id ); ?>-title">
	<div class="mgd-seo-tool__intro">
		<h2 id="<?php echo esc_attr( $instance_id ); ?>-title"><?php esc_html_e( 'SEO-Werkzeuge', 'mgd-wp-seo-tool' ); ?></h2>
		<p><?php esc_html_e( 'Prüfen, optimieren und teilen – wähle das passende Werkzeug.', 'mgd-wp-seo-tool' ); ?></p>
	</div>

	<?php $tabs = array( 'snippet' => 'Snippet', 'social' => 'Social', 'audit' => 'URL-Audit', 'schema' => 'Struktur & Schema', 'media' => 'Bilder & Links', 'index' => 'Indexierung', 'campaign' => 'Kampagnenlink' ); ?>
	<div data-shared-url class="mgd-seo-tool__shared-url"></div>
	<div class="mgd-seo-tool__tabs" role="tablist" aria-label="SEO-Werkzeuge" hidden>
		<?php foreach ( $tabs as $tab => $label ) : ?>
			<button type="button" role="tab" id="<?php echo esc_attr( $instance_id . '-tab-' . $tab ); ?>" aria-controls="<?php echo esc_attr( $instance_id . '-panel-' . $tab ); ?>" aria-selected="<?php echo 'snippet' === $tab ? 'true' : 'false'; ?>" tabindex="<?php echo 'snippet' === $tab ? '0' : '-1'; ?>" data-tab="<?php echo esc_attr( $tab ); ?>"><?php echo esc_html( $label ); ?></button>
		<?php endforeach; ?>
	</div>
	<div class="mgd-seo-tool__grid" data-panel="snippet" id="<?php echo esc_attr( $instance_id ); ?>-panel-snippet" role="tabpanel" aria-labelledby="<?php echo esc_attr( $instance_id ); ?>-tab-snippet">
		<div class="mgd-seo-tool__editor">
			<div class="mgd-seo-tool__field">
				<div class="mgd-seo-tool__label-row"><label for="<?php echo esc_attr( $instance_id ); ?>-title-input"><?php esc_html_e( 'Meta-Titel', 'mgd-wp-seo-tool' ); ?></label><button type="button" class="mgd-seo-tool__info" data-info-open="title" aria-label="Tipps zum Meta-Titel">i</button></div>
				<input id="<?php echo esc_attr( $instance_id ); ?>-title-input" class="mgd-seo-tool__input" type="text" maxlength="120" autocomplete="off" placeholder="z. B. Webdesign aus Plauen – Michael Gahn DESIGN" data-field="title" aria-describedby="<?php echo esc_attr( $instance_id ); ?>-title-help">
				<p class="mgd-seo-tool__hint" id="<?php echo esc_attr( $instance_id ); ?>-title-help"><span data-title-count>0</span> / 60 Zeichen als Orientierung · Eingabelimit: 120</p>
			</div>

			<div class="mgd-seo-tool__field">
				<div class="mgd-seo-tool__label-row"><label for="<?php echo esc_attr( $instance_id ); ?>-description-input"><?php esc_html_e( 'Meta-Beschreibung', 'mgd-wp-seo-tool' ); ?></label><button type="button" class="mgd-seo-tool__info" data-info-open="description" aria-label="Tipps zur Meta-Beschreibung">i</button></div>
				<textarea id="<?php echo esc_attr( $instance_id ); ?>-description-input" class="mgd-seo-tool__input" rows="5" maxlength="320" placeholder="Beschreibe den Nutzen der Seite in einem klaren, einladenden Satz." data-field="description" aria-describedby="<?php echo esc_attr( $instance_id ); ?>-description-help"></textarea>
				<p class="mgd-seo-tool__hint" id="<?php echo esc_attr( $instance_id ); ?>-description-help"><span data-description-count>0</span> / 160 Zeichen als Orientierung · Eingabelimit: 320</p>
			</div>

			<div class="mgd-seo-tool__field">
				<label for="<?php echo esc_attr( $instance_id ); ?>-url-input"><?php esc_html_e( 'Vorschau-URL', 'mgd-wp-seo-tool' ); ?></label>
				<div class="mgd-seo-tool__url-control">
					<input id="<?php echo esc_attr( $instance_id ); ?>-url-input" class="mgd-seo-tool__input" type="url" inputmode="url" value="<?php echo esc_attr( $default_url ); ?>" placeholder="https://example.de/seite/" data-field="url" aria-describedby="<?php echo esc_attr( $instance_id ); ?>-url-help">
					<button class="mgd-seo-tool__button mgd-seo-tool__button--compact" type="button" data-action="fetch-social"><?php esc_html_e( 'Socialdaten laden', 'mgd-wp-seo-tool' ); ?></button>
				</div>
				<p class="mgd-seo-tool__hint" id="<?php echo esc_attr( $instance_id ); ?>-url-help"><?php esc_html_e( 'Diese Adresse gilt für Vorschau und Analyse. Ein Abruf erfolgt nur auf deinen Klick.', 'mgd-wp-seo-tool' ); ?></p>
			</div>

			<div class="mgd-seo-tool__field">
				<div class="mgd-seo-tool__label-row"><label for="<?php echo esc_attr( $instance_id ); ?>-keyword-input"><?php esc_html_e( 'Fokus-Keyword (optional)', 'mgd-wp-seo-tool' ); ?></label><button type="button" class="mgd-seo-tool__info" data-info-open="keyword" aria-label="Tipps zum Fokus-Keyword">i</button></div>
				<input id="<?php echo esc_attr( $instance_id ); ?>-keyword-input" class="mgd-seo-tool__input" type="text" autocomplete="off" placeholder="z. B. Webdesign Plauen" data-field="keyword">
			</div>

			<div class="mgd-seo-tool__actions" aria-label="<?php esc_attr_e( 'Werkzeugaktionen', 'mgd-wp-seo-tool' ); ?>">
				<button class="mgd-seo-tool__button mgd-seo-tool__button--primary" type="button" data-action="copy"><?php esc_html_e( 'Meta-Daten kopieren', 'mgd-wp-seo-tool' ); ?></button>
				<button class="mgd-seo-tool__button" type="button" data-action="copy-tags"><?php esc_html_e( 'Meta-Tags kopieren', 'mgd-wp-seo-tool' ); ?></button>
				<button class="mgd-seo-tool__button" type="button" data-action="save"><?php esc_html_e( 'Entwurf lokal speichern', 'mgd-wp-seo-tool' ); ?></button>
				<button class="mgd-seo-tool__button mgd-seo-tool__button--quiet" type="button" data-action="reset"><?php esc_html_e( 'Zurücksetzen', 'mgd-wp-seo-tool' ); ?></button>
			</div>
			<p class="mgd-seo-tool__status" data-status role="status" aria-live="polite"></p>
		</div>

		<div class="mgd-seo-tool__previews">
			<div class="mgd-seo-tool__preview-card">
				<p class="mgd-seo-tool__preview-label"><?php esc_html_e( 'Suchvorschau', 'mgd-wp-seo-tool' ); ?></p>
				<div class="mgd-seo-tool__preview-options">
					<label>Suchmaschine <select class="mgd-seo-tool__input" data-preview-engine><option value="google">Google</option><option value="bing">Bing</option></select></label>
					<label>Darstellung <select class="mgd-seo-tool__input" data-preview-device><option value="desktop">Desktop</option><option value="mobile">Mobil</option></select></label>
				</div>
				<p class="mgd-seo-tool__hint mgd-seo-tool__preview-note">Eine Annäherung: Suchmaschinen passen Texte und Darstellung je nach Suche und Gerät an.</p>
				<div class="mgd-seo-tool__serp" data-preview-engine-view="google" data-preview-device-view="desktop" aria-live="polite">
					<p class="mgd-seo-tool__serp-url" data-preview-url><?php echo esc_html( $default_url ); ?></p>
					<p class="mgd-seo-tool__serp-title" data-preview-title><?php esc_html_e( 'Dein Seitentitel erscheint hier', 'mgd-wp-seo-tool' ); ?></p>
					<p class="mgd-seo-tool__serp-description" data-preview-description><?php esc_html_e( 'Deine Meta-Beschreibung erscheint hier als kurze Zusammenfassung der Seite.', 'mgd-wp-seo-tool' ); ?></p>
				</div>
			</div>

			<div class="mgd-seo-tool__preview-card mgd-seo-tool__preview-card--social">
				<p class="mgd-seo-tool__preview-label"><?php esc_html_e( 'Social-Vorschau', 'mgd-wp-seo-tool' ); ?></p>
				<div class="mgd-seo-tool__social-media" data-social-media>
					<div class="mgd-seo-tool__social-placeholder" data-social-placeholder aria-hidden="true"></div>
					<img class="mgd-seo-tool__social-image" data-social-image alt="" hidden>
				</div>
				<p class="mgd-seo-tool__social-domain" data-social-domain><?php echo esc_html( wp_parse_url( $default_url, PHP_URL_HOST ) ?: $default_url ); ?></p>
				<p class="mgd-seo-tool__social-title" data-social-title><?php esc_html_e( 'Dein Seitentitel erscheint hier', 'mgd-wp-seo-tool' ); ?></p>
				<p class="mgd-seo-tool__social-description" data-social-description><?php esc_html_e( 'Die Beschreibung wird für eine Vorschau in sozialen Netzwerken übernommen.', 'mgd-wp-seo-tool' ); ?></p>
				<button class="mgd-seo-tool__button mgd-seo-tool__button--compact mgd-seo-tool__social-apply" type="button" data-action="apply-social" disabled><?php esc_html_e( 'OG-Daten übernehmen', 'mgd-wp-seo-tool' ); ?></button>
			</div>

			<ul class="mgd-seo-tool__checks" data-checks aria-live="polite"></ul>
		</div>
	</div>
	<dialog class="mgd-seo-tool__dialog" data-info-dialog aria-labelledby="<?php echo esc_attr( $instance_id ); ?>-info-title">
		<div class="mgd-seo-tool__dialog-head"><h3 id="<?php echo esc_attr( $instance_id ); ?>-info-title" data-info-title></h3><button type="button" class="mgd-seo-tool__dialog-close" data-info-close aria-label="Hinweis schließen">×</button></div>
		<div data-info-body></div>
		<button type="button" class="mgd-seo-tool__button" data-info-close>Verstanden</button>
	</dialog>
	<?php foreach ( array_slice( $tabs, 1, null, true ) as $tab => $label ) : ?>
	<section class="mgd-seo-tool__panel" data-panel="<?php echo esc_attr( $tab ); ?>" id="<?php echo esc_attr( $instance_id . '-panel-' . $tab ); ?>" role="tabpanel" aria-labelledby="<?php echo esc_attr( $instance_id . '-tab-' . $tab ); ?>" hidden>
		<h3><?php echo esc_html( $label ); ?></h3>
		<?php if ( 'social' === $tab ) : ?>
		<p>So können Titel, Beschreibung und Bild beim Teilen wirken. Die tatsächliche Darstellung hängt von der Plattform ab.</p>
		<div data-social-slot></div>
		<?php elseif ( 'campaign' === $tab ) : ?>
		<div data-campaign-slot></div>
		<?php else : ?>
		<p>Prüfe die URL aus der Leiste oben. Die Analyse liest das ausgelieferte HTML. Inhalte, die erst JavaScript erzeugt, werden nicht erfasst. Ergebnisse können bis zu 12 Stunden alt sein.</p>
		<button class="mgd-seo-tool__button" type="button" data-action="analyze">URL analysieren</button>
		<div data-audit-output="<?php echo esc_attr( $tab ); ?>" role="status" aria-live="polite"><p>Noch keine Analyse. Trage oben eine URL ein und starte die Prüfung.</p></div>
		<?php if ( 'schema' === $tab ) : ?>
		<div class="mgd-seo-tool__generator" data-schema-generator>
			<h3>Schema-Generator</h3>
			<p>Erstelle strukturierte Daten zum Kopieren. Verwende nur Angaben, die auf der betreffenden Seite tatsächlich sichtbar sind. Prüfe vorhandene Schema-Daten, damit keine widersprüchlichen Einträge entstehen.</p>
			<label>Typ <select class="mgd-seo-tool__input" data-schema="type"><option value="Organization">Organisation</option><option value="LocalBusiness">Lokales Unternehmen</option><option value="Article">Artikel</option></select></label>
			<label>Name / Artikelüberschrift <input class="mgd-seo-tool__input" data-schema="name" maxlength="200" type="text"></label>
			<label>Seiten-URL <input class="mgd-seo-tool__input" data-schema="url" type="url" placeholder="https://example.de/"></label>
			<label>Bild- oder Logo-URL (optional) <input class="mgd-seo-tool__input" data-schema="image" type="url" placeholder="https://example.de/bild.jpg"></label>
			<div data-schema-address hidden><label>Straße und Hausnummer <input class="mgd-seo-tool__input" data-schema="street" type="text"></label><label>Postleitzahl <input class="mgd-seo-tool__input" data-schema="postal" type="text"></label><label>Ort <input class="mgd-seo-tool__input" data-schema="city" type="text"></label><label>Ländercode (z. B. DE) <input class="mgd-seo-tool__input" data-schema="country" type="text" maxlength="2"></label></div>
			<div data-schema-article hidden><label>Autor <input class="mgd-seo-tool__input" data-schema="author" type="text"></label><label>Veröffentlicht am <input class="mgd-seo-tool__input" data-schema="date" type="date"></label></div>
			<button type="button" class="mgd-seo-tool__button mgd-seo-tool__button--primary" data-schema-create>JSON-LD erstellen</button>
			<label>Ergebnis <textarea class="mgd-seo-tool__input" data-schema-result rows="12" readonly></textarea></label>
			<button type="button" class="mgd-seo-tool__button" data-schema-copy>Code kopieren</button><p role="status" data-schema-status></p>
			<p>Das Ergebnis ist ein Entwurf, keine Garantie für eine Darstellung bei Google. Teste die veröffentlichte Seite mit dem <a href="https://search.google.com/test/rich-results" target="_blank" rel="noopener noreferrer">Rich Results Test</a>.</p>
		</div>
		<div class="mgd-seo-tool__generator" data-utm-generator><h3>Kampagnenlink erstellen</h3><p>Ergänze Messparameter für deine Kampagne. Keine personenbezogenen oder vertraulichen Daten eingeben.</p>
		<label>Ziel-URL <input class="mgd-seo-tool__input" data-utm="url" type="url" placeholder="https://example.de/"></label><label>Quelle <input class="mgd-seo-tool__input" data-utm="source" type="text" placeholder="newsletter"></label><label>Medium <input class="mgd-seo-tool__input" data-utm="medium" type="text" placeholder="email"></label><label>Kampagne <input class="mgd-seo-tool__input" data-utm="campaign" type="text" placeholder="herbstaktion"></label><button type="button" class="mgd-seo-tool__button" data-utm-create>Link erstellen</button><label>Ergebnis <input class="mgd-seo-tool__input" data-utm-result readonly></label><button type="button" class="mgd-seo-tool__button" data-utm-copy>Link kopieren</button><p role="status" data-utm-status></p></div>
		<?php endif; ?>
		<?php endif; ?>
	</section>
	<?php endforeach; ?>
</section>
