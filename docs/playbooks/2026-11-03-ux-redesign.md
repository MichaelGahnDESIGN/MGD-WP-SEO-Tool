# SEO-Tool übersichtlicher gestalten

Ziel: Hauptaufgaben schnell auffindbar, durchgängige Bedienung, Desktop und Mobil geprüft; Funktionen von 0.3.0 bewahren. Grundlinie: main 63d35f4, 0.2.0 live, 0.3.0 lokal. Rückfall: 63d35f4 und Live-Release 0.2.0. Playtest-Artefakte und Branch bleiben lokal.

| ID | Schritt | Voraussetzung | Erfolg / Prüfung | Rückfall | Status | Beleg |
|---|---|---|---|---|---|---|
| U1 | Playtest und Agentenempfehlungen | Grundlinie | Erstkontakt, Navigation und Aufgaben überprüft; Befunde lokal dokumentiert | keine Live-Schreibdaten | geprüft | Zwei Agenten; lokaler Playtest und Bilder unter ignoriertem PLAYTEST |
| U2 | Plugin-Oberfläche und ggf. Seitenrahmen überarbeiten | U1 | klarer Einstieg, passende Aktionen, Generator erhalten; Desktop und Mobil visuell und funktional | 63d35f4 | in Arbeit | Plugin in lokaler Vorschau und WordPress mit sieben Tabs getestet; Live-Seite hat doppelte Einleitung und veraltete Anleitung. Divi-Seitenrahmen noch offen |
| U3 | Release und Integration | U2 | gezielte Tests, ZIP, Prüfsumme, GitHub-Release vor Live, Sicherung und Live-Abnahme | 0.2.0 / Updraft | geplant | Lokales ZIP erstellt; GitHub und Live offen |

## Live-Abnahme 03.11.2026

Updraft-Sicherung 00:38 mit Datenbank, Plugins, Themes, Uploads, unverzichtbaren Plugins und anderen Dateien vorhanden; nur auf dem Server gespeichert. Die SEO-Seite verwendet den klassischen Editor im Divi-Theme; doppelte Einleitung und veraltete Anleitung entfernt, Shortcode erhalten. Live-Update auf 0.3.1 über WordPress-Updatefunktion erfolgreich. Desktop: sieben Tabs, URL-Audit, OG-Bild, Snippet, Schema und Kampagnenlink geprüft. Mobil bei 390 px: kein Dokumentüberlauf; Navigation zu Schema und Kampagnenlink geprüft. Keine vollständige WCAG-Prüfung.

Vorfall: Release 0.3.0 enthielt versehentlich lokale Playtest-Artefakte. Release wurde sofort in Entwurf versetzt und beide Assets entfernt; lokales Testmaterial bleibt erhalten. Externer Zugriff während der kurzen Veröffentlichungszeit kann nicht ausgeschlossen werden. Paketierung korrigiert; 0.3.1 ZIP-Inhalt geprüft, GitHub-Download stimmt mit lokalem SHA-256 überein. Live-Rückfall: Updraft-Sicherung und 0.2.0-Release.
