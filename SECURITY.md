# Sicherheit

## Unterstützte Versionen

Sicherheitskorrekturen erfolgen für die jeweils aktuelle Release-Version.

## Updatequelle

Das Plugin fragt nur die öffentliche HTTPS-Release-API von
`MichaelGahnDESIGN/MGD-WP-SEO-Tool` ab. Akzeptiert werden ausschließlich:

- kein Entwurf und kein Vorab-Release,
- eine Versionsnummer im Format `vX.Y.Z` oder `X.Y.Z`,
- das Asset `mgd-wp-seo-tool-X.Y.Z.zip` mit dem erwarteten GitHub-Downloadpfad.

Bei einem Netzwerk-, Format- oder Validierungsfehler wird kein Update angeboten;
die installierte Version bleibt unverändert.

## Social-Vorschau und SSRF-Schutz

Der optionale AJAX-Endpunkt für **„Socialdaten laden“** akzeptiert ausschließlich
öffentliche HTTPS-URLs ohne Login-Daten oder Sonderport. Vor dem Abruf prüft das
Plugin Hostname und A-/AAAA-DNS-Ergebnisse auf private und reservierte Bereiche.
Der Abruf folgt keinen Weiterleitungen, ist auf sechs Sekunden und 256 KiB HTML
begrenzt und gibt niemals fremdes HTML zurück. Er extrahiert nur kurze,
bereinigte Textwerte sowie eine valide öffentliche HTTPS-`og:image`-URL.

Ein kurzlebiges Rate-Limit speichert ausschließlich einen mit WordPress-Salz
abgeleiteten Schlüssel – keine IP-Adresse. Ergebnisse werden je URL zwölf Stunden
zwischengespeichert. Ein DNS-Rebinding zwischen Vorprüfung und HTTP-Verbindung
bleibt eine Infrastrukturabhängigkeit des Webservers; `wp_safe_remote_get()` und
`reject_unsafe_urls` sind deshalb zusätzlich aktiv.

## Datenschutz

Meta-Daten werden im Browser verarbeitet. Eine Speicherung erfolgt nur nach
einem bewussten Klick im lokalen Browser-Speicher und kann über „Zurücksetzen“
entfernt werden. Die Social-Vorschau übermittelt nur die bewusst eingegebene URL
an den Server; die Zielseite erhält dessen Server-IP. Wird ein OG-Bild dargestellt,
lädt der Browser es direkt von der Zielseite; diese sieht dann die Besucher-IP.
Die Updateprüfung übermittelt keine Tool-Inhalte und keine Anmeldedaten; wie bei
jedem Abruf eines GitHub-Releases erhält GitHub die Server-IP der Website.
# HTML-Analyse 0.2.0

Der bestehende geschützte Social-Abruf liefert zusätzlich kurze, bereinigte Textwerte und Zähler. Keine Skriptausführung, keine verknüpften Ressourcen. Fremde Werte werden nur über Textknoten ausgegeben. Der zwölfstündige Cache enthält zusätzlich SEO-Metadaten und Überschriften. Antworten an der 256-KiB-Grenze werden abgelehnt. Redirects bleiben gesperrt; die endgültige HTTPS-Adresse ist erforderlich.

Gezielte Quellprüfung durchgeführt, kein vollständiges Audit. DNS-/HTTP-Verhalten und AJAX-Integration müssen vor Live-Einsatz in einer WordPress-Testumgebung verifiziert werden.
