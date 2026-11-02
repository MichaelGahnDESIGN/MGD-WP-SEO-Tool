# SEO-Werkzeuge 0.2.0

## Vertrag
Fertig: sechs bedienbare Tabs, bestehende Vorschau erhalten, begrenzter URL-Abruf, lokale Prüfungen und Release-ZIP mit Prüfsumme. Stopp nach drei gezielten Versuchen je Blocker oder 20 Arbeitsrunden. Kein Push oder Live-Deployment ohne Freigabe.

## Grundlinie
Projekt: mgd-wp-seo-tool. Git: main, 0ad6004, sauber. Live zuletzt 0.1.1. Rückfall: Ausgangscommit und vorhandenes 0.1.1-Paket. Keine Datenbankmigration.

| ID | Schritt / Dateien | Voraussetzung | Erfolg und Prüfung | Rückfall | Status | Beleg |
|---|---|---|---|---|---|---|
| P1 | Begrenzte HTML-Analyse, includes | Grundlinie | Metadaten, Struktur, Bilder, Links und Indexierung aus Fixture korrekt | neue Klasse entfernen | geprüft | php tests/analyzer.php: 12 Erwartungen erfüllt |
| P2 | Sechs Tabs, templates/assets | P1 | Auswahl, Pfeiltasten, Ausgaben, bestehende Vorschau; breite/schmale Sichtprüfung | Git-Diff zurücknehmen | geprüft | Desktop/390px ohne Überlauf; Pfeiltaste geprüft. Echte lokale WordPress-Installation: AJAX-Ergebnisse für alle vier Analysen, OG-Bild geladen (1200px), OG-Titel übernommen |
| P3 | Dokumentation, Version, Paket | P2 | Syntax, Paketinhalt und SHA-256 gültig | 0.1.1 | geprüft | PHP-/JS-Syntax, ZIP-Test und SHA-256 gültig; Paket noch nicht live freigegeben |

Sicherheitsprüfung: gezielte Quellprüfung; kein vollständiges Audit. URL-Prüfungen, Rate-Limit und Redirect-Verbot bleiben erhalten. Server analysiert ausgeliefertes HTML, kein JavaScript-Rendering. Keine Behauptung über tatsächliche Google-Indexierung.

Apple Design: gemeinsame Bedienleiste, Systemtypografie, zurückhaltende Flächen, direktes Druckfeedback, Textskalierung und Kontrastmodus. ECC: Tab-Rollen, Fokusführung, Pfeiltasten, verbundene Formularlabels.

## Übergabe

## Veröffentlichung und Live-Abnahme am 03.11.2026
Michael hat Veröffentlichung und Installation ausdrücklich freigegeben. Tag v0.2.0 zeigt auf 603a4fb. Öffentliches GitHub-Release mit ZIP und Prüfsumme veröffentlicht; heruntergeladenes ZIP entspricht dem lokalen Paket. WordPress erkannte 0.2.0 über die eigene Updateprüfung und installierte es erfolgreich über den normalen Aktualisierungsweg. Updraft-Sicherung vom 02.11.2026 04:21 mit Datenbank, Plugins, Themes, Uploads und weiteren Dateien vorab geprüft; keine neue Sicherung erstellt.

Live auf /seo-tool/: sechs Tabs sichtbar; URL-Audit liefert HTTP 200, Titel, Beschreibung, H1, Sprache und Viewport. Schema-Typen/Überschriften, Bild-/Linkzählung sowie Canonical/Robots geprüft. OG-Bild vollständig geladen (1200px), OG-Titel in Snippet übernommen. Release und Live-Abnahme abgeschlossen. Grenzen (kein JavaScript-Rendering, keine echte Google-Indexprüfung, keine vollständige Schema-Validierung) bleiben dokumentiert.

Planänderung 03.11.: P2 erhält eine echte lokale WordPress-/MariaDB-Testinstallation unter .tmp/wp-test. Der Abruf der Divi-Startseite übersteigt 256 KiB. Die feste Grenze steigt deshalb auf 2 MiB, weiterhin ohne Redirects und mit sechs Sekunden Timeout. Erfolgsmaßstab bleibt vollständiges HTML statt abgeschnittener Ergebnisse.
Lokales Paket 0.2.0 erstellt, kein Push, kein GitHub-Release, kein Live-Upload. P1–P3 lokal geprüft. Die zusätzliche echte WordPress-Testinstallation unter .tmp/wp-test wurde nur über Loopback angesprochen. Testdatenbank verwendet einen eigenen Datenordner und keinen Netzwerkport. Keine produktive Datenbank verändert. GitHub-Veröffentlichung und Live-Installation sind gemäß Autopilot separate Freigabeschritte. Rückfall für die Live-Seite bleibt das Paket 0.1.1 und eine vor Installation erneut zu kontrollierende Updraft-Sicherung.
