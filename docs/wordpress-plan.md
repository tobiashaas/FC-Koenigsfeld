# FC Königsfeld · Umsetzungsplan WordPress

Stand: 21.10.2026. Grundlage sind die Entwürfe in Paper (Startseite, 9 Unterseiten, alle auch mobil), das Konzept in `redesign-konzept.md`, eine Prüfung der Pipeline `fussball.de-2-wordpress` und eine Prüfung von Etch 1.5.1 (Quellcode und Doku).

**Ziel:** So viel wie möglich pflegt sich selbst. Spieldaten kommen automatisch, alles andere pflegen die Teams einmal an einer Stelle, und jede Seite zieht es von dort.

---

## 1. Begriffe in einem Satz

| Begriff | Was es ist | Beispiel beim FCK |
|---|---|---|
| **CPT** (Custom Post Type) | Eine eigene Inhaltsart neben „Beiträge“ und „Seiten“. | „Mannschaft“, „Spiel“, „Spieler“ – jede mit eigener Liste im Admin. |
| **Taxonomie** | Eine Schublade zum Sortieren und Filtern. | News bekommen die Kategorie „Jugend“ und das Team „E-Junioren“. |
| **Feld** (Custom Field) | Ein Zusatzwert an einem Eintrag. | Spieler: Rückennummer, Position. Spiel: Anstoß, Tore. |
| **Relationship** | Eine Verknüpfung zwischen zwei Einträgen. | Spiel → Mannschaft. Die Teamseite zeigt dadurch automatisch ihre Spiele. |
| **Repeater** | Ein Feld, das man beliebig oft wiederholen kann. | Trainingszeiten: Tag, Uhrzeit, Platz – so viele Zeilen wie nötig. |
| **Options-Seite** | Eine Einstellungsseite für die ganze Website. | Aktuelle Saison, Social-Links, Mitgliedsbeiträge. |
| **Loop** | Eine Abfrage in Etch, die Einträge wiederholt ausgibt. | „Die nächsten 5 Spiele dieses Teams.“ |

---

## 2. Technik

| Baustein | Entscheidung | Warum |
|---|---|---|
| WordPress | aktuell, eigener Hoster | gibt es schon |
| Etch | Page-Builder für alle Templates | Templates als Code-nahes HTML, Loops mit echten `WP_Query`-Argumenten |
| AutomaticCSS | Design-Tokens, Abstände, Typo | unsere Tokens sind bereits ACSS-nah |
| **ACF Pro** (Empfehlung) | Felder, Repeater, Relationships, Options-Seiten | bester Etch-Adapter, Relationships liegen in normaler Post-Meta → per `meta_query` abfragbar, ca. 49 $/Jahr (Preis unverifiziert) |
| Plugin `fck-core` (eigen, im Git) | CPTs, Taxonomien, Feldgruppen, Rechenwerte, Sync-Endpoint | Datenmodell versioniert und unabhängig vom Builder |
| n8n | holt Daten von fussball.de | gibt es schon, wird umgebaut |

**Offene Entscheidung ACF Pro oder Meta Box:** Die alte Seite und die Pipeline nutzen heute Meta Box. Meta Box geht auch, aber MB-Relationships liegen in einer eigenen Tabelle und brauchen in Etch Sonder-Loops (`mbFrom`/`mbTo`). Weil `fck-core` alles per Code registriert und die Pipeline künftig über einen eigenen Endpoint schreibt, kostet der Wechsel zu ACF wenig. Endgültig entscheiden, nachdem wir die alte Seite per SSH gesehen haben.

---

## 3. Inhaltsmodell

### 3.1 Inhaltsarten (CPTs)

| CPT | Wichtige Felder | Verknüpft mit | Wer pflegt |
|---|---|---|---|
| **Mannschaft** | Name, Kurzname, Altersklasse, Jahrgänge, Liga/Staffel, `fd_team_id` (leer = keine Liga), Teamfoto, Trainingszeiten (Repeater: Tag, von, bis, Spielstätte), Zonen (Plätze Aufstieg/Relegation/Abstieg), Sortierung, „auf Startseite“, Trikotsponsor | Spielstätte, Trainer:innen, Ansprechperson, Sponsor | Teams, einmal pro Saison |
| **Spiel** | `fd_spiel_id`, Anstoß (lokal, sortierbar), Spieltag, Wettbewerb, Heim (ja/nein), Gegner, Gegner-Wappen (Mediathek), Tore eigen/Gegner (Zahl), Zusatz (n.V./i.E.), Status (geplant/beendet/verlegt/abgesagt), Quelle (fussball.de/manuell) | Mannschaft, Spielstätte, Spielbericht (Beitrag) | **Pipeline**; bei Teams ohne Liga das Team |
| **Tabelle** | eine Tabelle pro Mannschaft als Liste: Platz, Verein, Sp, S, U, N, Tore, Diff, Pkt, eigenes Team, Zone | Mannschaft | **Pipeline** |
| **Person** | Vorname, Nachname, Foto, Rolle(n) (Spieler, Trainer, Betreuer, Jugendleitung, Vorstand, Kinderschutz), Telefon, E-Mail, Einwilligung (Datum), erwachsen (ja/nein) | Mannschaft(en) | Teams / Vorstand |
| **Spieler-Profil** (Felder an Person) | Rückennummer, Position, im Verein seit, starker Fuß, Lieblingsmoment, Saisonzahlen | Mannschaft | Spieler selbst über das Team |
| **Spielstätte** | Name, Adresse, Koordinaten, Belag, Parken, Hinweise für Gäste, Telefon am Spieltag | – | Vorstand, einmalig |
| **Partner** | Name, Logo (SVG), Stufe (Haupt/Exklusiv/Premium/Ausstattung/Bande-Plus/Bande/Basis), Rolle („Trikot SG B.K.N.“), Website, Ort, Laufzeit bis | Mannschaft (z. B. Trikot) | Vorstand |
| **Termin** | Start, Ende, ganztägig, Ort, Text, Veranstalter (Verein/Förderverein) | Spielstätte | Vorstand / Förderverein |
| **Frage** (FAQ) | Frage, Antwort, Bereich (Eltern/Partner/Mitglied) | – | Jugendleitung |
| **Download** | Titel, Datei, Bereich | – | Vorstand |

**Beiträge (News)** bleiben normale WordPress-Beiträge. Sie bekommen eine Kategorie, eine Taxonomie „Mannschaft“ und optional die Verknüpfung „zu Spiel“ (dann ist es ein Spielbericht).

### 3.2 Taxonomien

- **Kategorie** (Beiträge): Spielbericht, Jugend, Mädchen, Verein, Förderverein, Turniere.
- **Mannschaft** (Beiträge, Termine, Downloads): spiegelt die Mannschaften. So filtern Teamseite und News nach Team.
- **Altersklasse** (Mannschaften): Herren, Ü35, A–F, Bambini, Mädchen. Neue Teams einfach anlegen.
- **Position** (Personen): Tor, Abwehr, Mittelfeld, Sturm. Daraus entstehen die Kader-Gruppen.

### 3.3 Options-Seite „Verein“

Aktuelle Saison · Winter- und Sommerpause (von/bis) · Mitgliedsbeiträge (Jugend, Erwachsene, Familie) · Social-Links · Vereinsheim-Telefon · Anzahl Teams und Plätze · Instagram-Handle.

### 3.4 Verknüpfungen auf einen Blick

```
Mannschaft ─┬─< Spiel >── Spielstätte
            ├─< Tabelle
            ├─< Person (Spieler, Trainer, Ansprechperson)
            ├── Spielstätte (Heimplatz, Trainingsorte)
            └── Partner (Trikot)
Spiel ────── Beitrag (Spielbericht)
Beitrag ──── Mannschaft (Taxonomie)
```

**Regel für Etch:** Eine Verknüpfung, nach der gefiltert wird, steht als einzelne ID im Feld des „Kind“-Eintrags (z. B. Spiel → `team_id`). Dann reicht in Etch ein einfaches `meta_query` mit `$team: this.id`.

---

## 4. Was automatisch läuft

| Bereich | Quelle | Wie oft |
|---|---|---|
| Spiele, Ergebnisse, Tabellen der Teams mit Liga | Pipeline (fussball.de) | Sa/So 12–20 Uhr alle 30 Min., sonst 2× täglich |
| Spiele von Teams ohne Liga | das Team, gleiche Maske | bei Bedarf |
| Nächstes / letztes Spiel je Team, Form (S/U/N), Zonen, „heute“-Badge, Leerzustände | `fck-core` rechnet nach jedem Sync | automatisch |
| Spielplan nach Tagen, nächstes Spiel über alle Teams | `fck-core` stellt fertige Listen bereit (Etch-Filter `etch/dynamic_data/option`, gecacht) | automatisch |
| Partner ausblenden nach Laufzeit-Ende | `fck-core` | automatisch |
| Termin-Kachel, Weihnachtsmarkt usw. | nächster Termin aus dem CPT | automatisch |
| Spieler-Saisonzahlen | Pipeline, nur für Personen mit Einwilligung und erwachsen; sonst manuell | automatisch |
| Kader, Trainer, Trainingszeiten, Jugendleitung | einmal im CPT, erscheint auf Startseite, Teamseite, Jugendseite, Kontakt | einmal pro Saison |

Die Pipeline legt **nie** selbst Personen an und schreibt **nie** leere Daten über gute Daten.

---

## 5. Seiten → Templates und Loops

| Seite (Paper) | Etch-Template | Datenquelle |
|---|---|---|
| Startseite | Seite „Start“ | `options.fck.naechstes_spiel` je Team, Tabelle der gewählten Mannschaft, 4 neueste Beiträge, nächster Termin, Partner nach Stufe, Mitgliedsbeiträge aus Optionen |
| Aktuelles · Übersicht | Archiv Beiträge | Main Query + Kategorie/Team aus URL-Parameter (Filter-Chips) |
| Artikel | Single Beitrag | Beitrag; wenn „zu Spiel“ gesetzt: Spieldaten-Kasten und nächstes Spiel |
| Teams · Übersicht | Archiv Mannschaft | Mannschaften nach Altersklasse und Sortierung |
| Teamseite | Single Mannschaft | Spiele mit `team_id = this.id`, Tabelle, Personen nach Position, Trainingszeiten (Repeater), Trikotsponsor |
| Spielerprofil | Single Person | nur wenn Einwilligung und erwachsen, sonst 404 |
| Spielplan | Seite „Spielplan“ | `options.fck.spiele_nach_tag` (gruppiert), Filter Team / Kommende / Ergebnisse |
| Jugend & Eltern | Seite | Mannschaften der Jugend mit Trainingszeiten, Fragen „Eltern“, Person mit Rolle Jugendleitung/Kinderschutz |
| Partner | Seite | Partner nach Stufe, Suche, Pakete als Fragen/Inhalt |
| Kontakt | Seite | Personen nach Rolle, Spielstätten, Downloads, Formular |

**Etch-Grenzen und unsere Lösung:**
- Kein Sortieren, Gruppieren oder Filtern im Template → `fck-core` liefert fertige Listen.
- Leere Loops zeigen nichts → zweiter Block mit Bedingung `length().equal(0)` für die Leerzustände.
- Pagination und Facets sind in Etch erst geplant → „Mehr laden“ vorerst als Link mit Seitenparameter; bei Bedarf WP Grid Builder.
- Etch lädt pro Loop-Eintrag alle Felder → Loops klein halten (`posts_per_page`).

---

## 6. Pipeline: Zielbild und Schritte

**Heute:** Logik nur im n8n-Workflow (nicht im Git), Spiele als ein serialisierter Block pro Team, es fehlen Heim/Auswärts, Ort, Spieltag, Status, Spiel-ID. Fehler können gute Daten leer überschreiben. Feldgruppen hängen auf `main` noch am falschen CPT.

**Ziel:** n8n holt und parst nur noch, `fck-core` schreibt.

1. Fix-Branch (`fix/mb-post-types-live-slug`) prüfen und mergen, n8n-Workflow ohne Zugangsdaten ins Repo exportieren.
2. Bug im Font-Mapping beheben (`"hyphen": ":"` macht aus `-:-` ein `:::`) und nach jedem Lauf eine Plausibilitätsprüfung. Schlägt sie fehl: nichts schreiben, Nachricht an den Admin.
3. **Rechtliches klären:** Ob das automatische Auslesen von fussball.de erlaubt ist, ist nicht geprüft. Mit SBFV/DFB klären. Rückfallweg: offizielle fussball.de-Widgets mit Zwei-Klick-Einwilligung.
4. `fck-core` bauen: CPTs, Felder, Endpoint `POST /fck/v1/sync` (Upsert über `fd_spiel_id`, prüft Daten, schreibt nie leer über gut).
5. n8n-Parser auf das Spiel-Schema umstellen: ID, Heim-Flag, Spieltag, Ort, Status, Tore als Zahl, Zeit mit Zeitzone. Torschützen nur bei Erwachsenen.
6. Eigener WordPress-Benutzer für den Sync mit nur dieser einen Berechtigung.
7. Häufiger synchronisieren (siehe 4), 1–2 Sekunden Pause zwischen Abrufen.
8. Alte Felder und MB-Views erst nach dem Umbau entfernen.

---

## 7. Migration der alten Seite (nach SSH-Zugang)

1. **Inventur, nur lesend:** WordPress-Version, Theme, Plugins, CPTs, Feldgruppen, Anzahl Beiträge/Seiten/Medien, Menüs, Formulare, Größe der Uploads.
2. **Was übernommen wird:** Beiträge (mit Datum, Kategorie, Beitragsbild), Medien, Partner-Logos, Teams, Ansprechpartner, Downloads (Satzung, Anträge).
3. **Wie:** Neue Seite als Staging aufsetzen, Beiträge per WP-CLI/WXR-Export importieren, Medien per rsync kopieren, alte Kategorien auf die neuen abbilden.
4. **Weiterleitungen:** Alle alten URLs auf die neuen (301), damit Google-Links und geteilte Links weiter funktionieren.
5. **Aufräumen:** Bilder ohne Einwilligung (Kinder!) beim Import markieren und prüfen.

---

## 8. Rollen

| Rolle | Darf |
|---|---|
| Admin | alles |
| Vorstand / Redaktion | Beiträge, Termine, Partner, Downloads, Optionen |
| Trainer:in | nur die eigene Mannschaft: Personen, Trainingszeiten, Spiele ohne Liga, Beiträge mit eigenem Team |
| Sync (technisch) | nur `fck/v1/sync` |

„Nur die eigene Mannschaft“ setzt `fck-core` über eine Zuordnung Person → Mannschaft um.

---

## 9. Datenschutz

- Keine Spielerprofile für Minderjährige, kein Geburtsdatum.
- Spielerprofil nur mit Einwilligung (Datum im Feld), sonst nicht abrufbar.
- Keine Torschützen-Namen bei Jugendspielen.
- Gegner-Wappen in die eigene Mediathek laden, kein Hotlinking.
- Instagram und Karten nur mit Zwei-Klick-Einwilligung.

---

## 10. Reihenfolge

| Phase | Inhalt | Ergebnis |
|---|---|---|
| 0 | SSH-Inventur der alten Seite, Entscheidung ACF/Meta Box, Rechtsfrage fussball.de anstoßen | Bestandsliste, Entscheidung |
| 1 | Staging-Seite, `fck-core` mit CPTs/Feldern/Optionen, ACSS-Tokens aus den Entwürfen | leeres, sauberes Datenmodell |
| 2 | Etch: Header, Footer, Komponenten (Spielkarte, Tabelle, Kachel, Partner-Zeile, Person), dann die Templates | alle Seiten mit Testdaten |
| 3 | Pipeline umbauen und an `fck/v1/sync` anschließen, Rechenwerte in `fck-core` | echte Spieldaten |
| 4 | Migration: Beiträge, Medien, Partner, Personen; Weiterleitungen | Inhalte drin |
| 5 | Teams schulen (Trainer-Rolle), Abnahme, Umzug auf die Live-Domain | live |

---

## 11. Offene Punkte

- ACF Pro oder Meta Box (nach SSH-Inventur).
- Erlaubnis für das Auslesen von fussball.de.
- Liefert fussball.de Spieler-Saisonzahlen überhaupt? Sonst manuell.
- Echte Inhalte vom Verein: Logos (SVG), Fotos, Namen von Jugendleitung und Vertrauensperson, Trainingszeiten, Adressen Neuhausen/Buchenberg, Satzung und Kinderschutzkonzept, Sponsorenmappe.
