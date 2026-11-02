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
		<p class="mgd-seo-tool__eyebrow"><?php esc_html_e( 'SEO-Werkzeug', 'mgd-wp-seo-tool' ); ?></p>
		<h2 id="<?php echo esc_attr( $instance_id ); ?>-title"><?php esc_html_e( 'Deine Seite. Klarer im Blick.', 'mgd-wp-seo-tool' ); ?></h2>
		<p><?php esc_html_e( 'Gestalte dein Suchergebnis, prüfe die Vorschau beim Teilen und entdecke technische SEO-Hinweise. Entwürfe bleiben auf deinem Gerät. Eine URL wird erst abgerufen, wenn du die Analyse oder Social-Vorschau startest.', 'mgd-wp-seo-tool' ); ?></p>
	</div>

	<?php $tabs = array( 'snippet' => 'Snippet', 'social' => 'Social', 'audit' => 'URL-Audit', 'schema' => 'Struktur & Schema', 'media' => 'Bilder & Links', 'index' => 'Indexierung' ); ?>
	<div data-shared-url class="mgd-seo-tool__shared-url"></div>
	<div class="mgd-seo-tool__tabs" role="tablist" aria-label="SEO-Werkzeuge" hidden>
		<?php foreach ( $tabs as $tab => $label ) : ?>
			<button type="button" role="tab" id="<?php echo esc_attr( $instance_id . '-tab-' . $tab ); ?>" aria-controls="<?php echo esc_attr( $instance_id . '-panel-' . $tab ); ?>" aria-selected="<?php echo 'snippet' === $tab ? 'true' : 'false'; ?>" tabindex="<?php echo 'snippet' === $tab ? '0' : '-1'; ?>" data-tab="<?php echo esc_attr( $tab ); ?>"><?php echo esc_html( $label ); ?></button>
		<?php endforeach; ?>
	</div>
	<div class="mgd-seo-tool__grid" data-panel="snippet" id="<?php echo esc_attr( $instance_id ); ?>-panel-snippet" role="tabpanel" aria-labelledby="<?php echo esc_attr( $instance_id ); ?>-tab-snippet">
		<div class="mgd-seo-tool__editor">
			<div class="mgd-seo-tool__field">
				<label for="<?php echo esc_attr( $instance_id ); ?>-title-input"><?php esc_html_e( 'Meta-Titel', 'mgd-wp-seo-tool' ); ?></label>
				<input id="<?php echo esc_attr( $instance_id ); ?>-title-input" class="mgd-seo-tool__input" type="text" maxlength="120" autocomplete="off" placeholder="z. B. Webdesign aus Plauen – Michael Gahn DESIGN" data-field="title" aria-describedby="<?php echo esc_attr( $instance_id ); ?>-title-help">
				<p class="mgd-seo-tool__hint" id="<?php echo esc_attr( $instance_id ); ?>-title-help"><span data-title-count>0</span> <?php esc_html_e( 'Zeichen · Richtwert: etwa 50–60 Zeichen', 'mgd-wp-seo-tool' ); ?></p>
			</div>

			<div class="mgd-seo-tool__field">
				<label for="<?php echo esc_attr( $instance_id ); ?>-description-input"><?php esc_html_e( 'Meta-Beschreibung', 'mgd-wp-seo-tool' ); ?></label>
				<textarea id="<?php echo esc_attr( $instance_id ); ?>-description-input" class="mgd-seo-tool__input" rows="5" maxlength="320" placeholder="Beschreibe den Nutzen der Seite in einem klaren, einladenden Satz." data-field="description" aria-describedby="<?php echo esc_attr( $instance_id ); ?>-description-help"></textarea>
				<p class="mgd-seo-tool__hint" id="<?php echo esc_attr( $instance_id ); ?>-description-help"><span data-description-count>0</span> <?php esc_html_e( 'Zeichen · Richtwert: etwa 140–160 Zeichen', 'mgd-wp-seo-tool' ); ?></p>
			</div>

			<div class="mgd-seo-tool__field">
				<label for="<?php echo esc_attr( $instance_id ); ?>-url-input"><?php esc_html_e( 'Vorschau-URL', 'mgd-wp-seo-tool' ); ?></label>
				<div class="mgd-seo-tool__url-control">
					<input id="<?php echo esc_attr( $instance_id ); ?>-url-input" class="mgd-seo-tool__input" type="url" inputmode="url" value="<?php echo esc_attr( $default_url ); ?>" placeholder="https://example.de/seite/" data-field="url" aria-describedby="<?php echo esc_attr( $instance_id ); ?>-url-help">
					<button class="mgd-seo-tool__button mgd-seo-tool__button--compact" type="button" data-action="fetch-social"><?php esc_html_e( 'Socialdaten laden', 'mgd-wp-seo-tool' ); ?></button>
				</div>
				<p class="mgd-seo-tool__hint" id="<?php echo esc_attr( $instance_id ); ?>-url-help"><?php esc_html_e( 'Lädt OG-Titel, Beschreibung und Bild nur auf deinen ausdrücklichen Klick. Das Bild wird danach direkt von der Zielseite geladen.', 'mgd-wp-seo-tool' ); ?></p>
			</div>

			<div class="mgd-seo-tool__field">
				<label for="<?php echo esc_attr( $instance_id ); ?>-keyword-input"><?php esc_html_e( 'Fokus-Keyword (optional)', 'mgd-wp-seo-tool' ); ?></label>
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
				<p class="mgd-seo-tool__preview-label"><?php esc_html_e( 'Google-Vorschau', 'mgd-wp-seo-tool' ); ?></p>
				<div class="mgd-seo-tool__serp" aria-live="polite">
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
	<?php foreach ( array_slice( $tabs, 1, null, true ) as $tab => $label ) : ?>
	<section class="mgd-seo-tool__panel" data-panel="<?php echo esc_attr( $tab ); ?>" id="<?php echo esc_attr( $instance_id . '-panel-' . $tab ); ?>" role="tabpanel" aria-labelledby="<?php echo esc_attr( $instance_id . '-tab-' . $tab ); ?>" hidden>
		<h3><?php echo esc_html( $label ); ?></h3>
		<?php if ( 'social' === $tab ) : ?>
		<p>So können Titel, Beschreibung und Bild beim Teilen wirken. Lade die Socialdaten über die URL-Leiste. Die tatsächliche Darstellung hängt von der Plattform ab.</p>
		<div data-social-slot></div>
		<?php else : ?>
		<p>Prüfe die URL aus der Leiste oben. Die Analyse liest das ausgelieferte HTML. Inhalte, die erst JavaScript erzeugt, werden nicht erfasst. Ergebnisse können bis zu 12 Stunden alt sein.</p>
		<button class="mgd-seo-tool__button" type="button" data-action="analyze">URL analysieren</button>
		<div data-audit-output="<?php echo esc_attr( $tab ); ?>" role="status" aria-live="polite"><p>Noch keine Analyse. Trage oben eine URL ein und starte die Prüfung.</p></div>
		<?php endif; ?>
	</section>
	<?php endforeach; ?>
</section>
