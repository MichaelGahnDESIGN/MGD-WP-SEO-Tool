# Changelog

## 0.1.1 - 2026-10-28

### Neu

- Optionaler, bewusst ausgelöster Social-Abruf für OG-Titel, OG-Beschreibung und `og:image`.
- OG-Daten können in den lokalen Entwurf übernommen werden.
- Kopierbarer HTML-Meta-Block inklusive Open-Graph-Tags.
- Ruhigere, arbeitsorientierte Oberfläche mit klarer Mobile-Anordnung und Statusrückmeldungen.

### Sicherheit und Datenschutz

- Öffentlicher HTTPS-only-Abruf mit Schutz vor internen und reservierten DNS-Zielen, ohne Weiterleitungen, mit HTML-/Größen-/Zeitlimit und temporärem Rate-Limit.
- Fremdes HTML wird nie an Besucher ausgegeben; nur bereinigte Metadaten werden verarbeitet.

## 0.1.0 - 2026-10-28

### Neu

- Erster veröffentlichungsfähiger Stand des MGD WP-SEO-Tools.
- Google- und Social-Snippet-Vorschau, Zeichenrichtwerte, Fokus-Keyword-Prüfung und Kopierfunktion.
- Optionales lokales Speichern von Entwürfen nur nach einem ausdrücklichen Klick.
- GitHub-Release-Updateprüfung mit manueller Prüfung im WordPress-Admin.

### Sicherheit und Datenschutz

- Keine Server-Speicherung von Tool-Eingaben, keine AJAX-Endpunkte und keine Tracking-Skripte.
- Updateprüfung akzeptiert ausschließlich semantisch versionierte Releases und ein exakt benanntes ZIP-Asset des fest hinterlegten GitHub-Repositories.
- Keine Tokens oder Zugangsdaten im Plugin.
# 0.2.0

- Sechs zugängliche Tabs mit gemeinsamer URL-Leiste und responsiver Oberfläche.
- HTML-Analyse: Titel, Beschreibung, H1, Sprache, Viewport, Canonical, Robots, JSON-LD-Typen, Bilder und Links.
- Ablehnung unvollständiger Antworten an der Größenobergrenze.
- HTML-Obergrenze von 2 MiB, damit auch größere Divi-Seiten vollständig analysiert werden können.
