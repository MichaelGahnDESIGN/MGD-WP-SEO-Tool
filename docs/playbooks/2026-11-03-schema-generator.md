# Schema-Generator und SEO-Hilfen

Ziel: lokal nutzbarer JSON-LD-Generator für Organization, LocalBusiness und Article sowie UTM-Link-Generator; barrierearm und ohne automatische Veröffentlichung. Fertig nach Funktions-, Syntax- und Paketprüfung; Stopp nach drei gezielten Versuchen je Blocker. Autopilot: Push und Live-Deployment nur nach Freigabe.

Grundlinie: Repository mgd-wp-seo-tool, main, 0b184a9, Arbeitsbaum sauber, Live 0.2.0. Rückfall: Git-Stand und Release 0.2.0. Keine Datenbankänderung.

| ID | Schritt | Erfolgskriterium | Status | Beleg |
|---|---|---|---|---|
| S1 | Generator und Kampagnenlinks | valides JSON-LD, sichere Ausgabe und korrekte URL-Parameter | geprüft | Organisation, LocalBusiness, Article, Pflichtfelder, escaped Script-Ende, UTM mit Query und Fragment im Browser geprüft |
| S2 | UI und Dokumentation | Tastatur, Desktop und Mobil geprüft | geprüft | 390 px ohne horizontalen Überlauf, Desktop sichtbar, Pfeiltaste und Tab-Fokus geprüft |
| S3 | Releasepaket | Tests, ZIP und SHA-256 | geprüft | PHP-/JS-Syntax, Parser-Fixture, ZIP-Integrität und Prüfsumme geprüft |

Lokal fertig. GitHub-Release und Live-Installation gemäß Autopilot gesondert freigeben. Die Vorschau wurde im Browser mit lokalen WordPress-Ausgabehilfen getestet; die Installation in echtem WordPress ist für diese Version noch offen. Vor Live-Installation Sicherung kontrollieren. Keine vollständige Schema-Validierung oder Google-Ergebnisgarantie.
