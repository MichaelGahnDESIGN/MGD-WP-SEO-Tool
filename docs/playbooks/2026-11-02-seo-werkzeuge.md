# SEO-Werkzeuge 0.2.0

## Vertrag
Fertig: sechs bedienbare Tabs, bestehende Vorschau erhalten, begrenzter URL-Abruf, lokale Prüfungen und Release-ZIP mit Prüfsumme. Stopp nach drei gezielten Versuchen je Blocker oder 20 Arbeitsrunden. Kein Push oder Live-Deployment ohne Freigabe.

## Grundlinie
Projekt: mgd-wp-seo-tool. Git: main, 0ad6004, sauber. Live zuletzt 0.1.1. Rückfall: Ausgangscommit und vorhandenes 0.1.1-Paket. Keine Datenbankmigration.

| ID | Schritt / Dateien | Voraussetzung | Erfolg und Prüfung | Rückfall | Status | Beleg |
|---|---|---|---|---|---|---|
| P1 | Begrenzte HTML-Analyse, includes | Grundlinie | Metadaten, Struktur, Bilder, Links und Indexierung aus Fixture korrekt | neue Klasse entfernen | geprüft | php tests/analyzer.php: 12 Erwartungen erfüllt |
| P2 | Sechs Tabs, templates/assets | P1 | Auswahl, Pfeiltasten, Ausgaben, bestehende Vorschau; breite/schmale Sichtprüfung | Git-Diff zurücknehmen | in Arbeit | Desktop/390px ohne Überlauf; Pfeiltaste geprüft. WordPress-AJAX-Integration noch offen |
| P3 | Dokumentation, Version, Paket | P2 | Syntax, Paketinhalt und SHA-256 gültig | 0.1.1 | geprüft | PHP-/JS-Syntax, ZIP-Test und SHA-256 gültig; Paket noch nicht live freigegeben |

Sicherheitsprüfung: gezielte Quellprüfung; kein vollständiges Audit. URL-Prüfungen, Rate-Limit und Redirect-Verbot bleiben erhalten. Server analysiert ausgeliefertes HTML, kein JavaScript-Rendering. Keine Behauptung über tatsächliche Google-Indexierung.

Apple Design: gemeinsame Bedienleiste, Systemtypografie, zurückhaltende Flächen, direktes Druckfeedback, Textskalierung und Kontrastmodus. ECC: Tab-Rollen, Fokusführung, Pfeiltasten, verbundene Formularlabels.

## Übergabe
Lokales Paket 0.2.0 erstellt, kein Push, kein GitHub-Release, kein Live-Upload. P2 bleibt offen für echte WordPress-AJAX-Integration. Lokale Vorschau bietet keine WordPress-Laufzeit. P3 prüft nur Paketmechanik, keine Freigabe zur Installation. Gesamtergebnis deshalb noch nicht vollständig abgenommen.
