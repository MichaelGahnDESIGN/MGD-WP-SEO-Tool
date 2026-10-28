# MGD WP-SEO-Tool

Ein eigenständiges, datensparsames WordPress-Plugin für einen Meta-Titel-/Meta-Beschreibungs-Check direkt auf der Website.

## Funktionen

- Google- und Social-Vorschau mit frei wählbarer Vorschau-URL
- bewusster Social-Abruf: OG-Titel, OG-Beschreibung und `og:image` einer öffentlichen HTTPS-URL anzeigen
- OG-Titel und -Beschreibung bei Bedarf in den Entwurf übernehmen
- kopierbarer HTML-Meta-Block inklusive Open-Graph-Tags
- Zeichenrichtwerte sowie optionale Fokus-Keyword-Prüfung
- Kopieren der vorbereiteten Meta-Daten
- optionaler, ausschließlich lokaler Entwurf im Browser
- zugängliche Formulare und Statusmeldungen
- keine Datenbanktabellen und keine Tracker

## Installation

1. Den Ordner `mgd-wp-seo-tool` als ZIP archivieren.
2. In WordPress unter **Plugins → Installieren → Plugin hochladen** hochladen und aktivieren.
3. Auf der gewünschten Seite den Shortcode einfügen:

   ```text
   [mgd_wp_seo_tool]
   ```

   Für Divi eignet sich ein Text- oder Code-Modul. Optional kann eine Standard-URL gesetzt werden:

   ```text
   [mgd_wp_seo_tool url="https://michael-gahn.de/seo-beratung/"]
   ```

## Datenschutz und Grenzen

Alle Texteingaben werden im Browser verarbeitet. Erst über den Button **„Entwurf lokal speichern“** schreibt das Tool die Werte in den Local Storage dieses Browsers; **„Zurücksetzen“** entfernt sie wieder.

Die Funktion **„Socialdaten laden“** ist ein bewusster, optionaler Abruf: Sie sendet nur die eingegebene öffentliche HTTPS-URL an die WordPress-Installation. Diese liest daraus ausschließlich OG-Titel, OG-Beschreibung und `og:image`; weder der HTML-Inhalt noch Formulareingaben werden gespeichert oder an den Browser zurückgegeben. Das angezeigte OG-Bild lädt der Browser anschließend direkt von der Zielseite. Dabei erhält die Zielseite die IP-Adresse des Besuchers – genau wie bei einem gewöhnlich eingebundenen Bild.

Zum Schutz der WordPress-Installation sind nur öffentliche HTTPS-Ziele ohne Anmeldung und ohne Sonderport zugelassen. Interne, private und reservierte DNS-Adressen, Weiterleitungen sowie zu große oder nicht als HTML deklarierte Antworten werden abgewiesen. Abrufe sind pro Besucher kurzzeitig begrenzt und Ergebnisse für dieselbe URL werden für zwölf Stunden zwischengespeichert.

Die Längen-Hinweise sind lediglich Darstellungsrichtwerte. Suchmaschinen bestimmen die endgültige Anzeige dynamisch; das Tool verspricht deshalb weder Platzierung noch eine unveränderte Snippet-Ausgabe.

## Entwicklung und Prüfung

Das Plugin setzt WordPress 6.5 und PHP 8.1 voraus. Vor einer Live-Auslieferung sollten PHP-Syntax, WordPress-Coding-Standards und die Darstellung in der konkreten Divi-Installation geprüft werden.

## Updates und Releases

WordPress prüft Updates ausschließlich über öffentliche Releases von
[`MichaelGahnDESIGN/MGD-WP-SEO-Tool`](https://github.com/MichaelGahnDESIGN/MGD-WP-SEO-Tool).
Die Prüfung akzeptiert nur ein semantisch versioniertes Release sowie das exakt
passende Paket `mgd-wp-seo-tool-X.Y.Z.zip`. Bei nicht erreichbaren oder
ungültigen Daten bleibt die installierte Version unverändert.

Vor einem Update empfiehlt sich eine geprüfte Sicherung, beispielsweise mit
UpdraftPlus. Ein manueller Check ist über **Dashboard → Aktualisierungen →
MGD-Updates jetzt prüfen** möglich; dieser Knopf installiert kein Update.

Das Release-Paket wird lokal mit folgendem Befehl erzeugt:

```bash
./tools/build-release.sh
```

Es landet unter `releases/` zusammen mit einer SHA-256-Prüfsumme. Das Paket
enthält ausschließlich die benötigten Plugin-Dateien, keine Git-Daten, lokalen
Arbeitsdateien, Tests oder Sicherungen.
