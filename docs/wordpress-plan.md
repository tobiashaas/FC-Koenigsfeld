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
| WordPress | neue Installation (Staging), später Umzug auf die Domain | alte Seite läuft auf IONOS mit Bricks |
| Etch | Page-Builder, **legt auch die Inhaltstypen (CPTs) und Taxonomien an** | Etch hat eigene Content Types; Loops mit echten `WP_Query`-Argumenten |
| AutomaticCSS + Frames | Design-Tokens, Abstände, Typo | beides läuft schon auf der alten Seite |
| **Meta Box** (inkl. MB Relationships) | Felder, die Etch selbst nicht kann | siehe unten |
| Plugin `fck-core` (eigen, im Git) | Rechenwerte (nächstes Spiel, Spiele nach Tag, Form), Sync-Endpoint für die Pipeline, Trainer-Rechte | Etch kann nicht sortieren, gruppieren oder filtern |
| Bricks2Etch | Migration von der alten Seite | überträgt Beiträge, Medien, CPTs, Meta-Box-Feldgruppen und -Werte |
| n8n | holt Daten von fussball.de | gibt es schon, wird umgebaut |

### Warum Etch-Felder allein nicht reichen

Etch kann eigene Felder anlegen, aber nur einfache Typen: Text, Textbereich, Zahl, Ja/Nein, Auswahl, Checkbox, Radio, Datum, Uhrzeit (Etch 1.5.1, Quellcode; die Public-API-Doku nennt nur text/textarea/number/boolean). Es fehlen **Bild/Logo, Datei, Repeater und Verknüpfungen (Relationship)**. Genau die brauchen wir: Teamfoto, Partner-Logo, Trainingszeiten, Spiel → Mannschaft, Person → Mannschaft.

### Warum Meta Box und nicht ACF

- Die alte Seite nutzt Meta Box schon (inkl. Relationships, Builder, Views). Die vorhandenen Feldgruppen und Werte (Mannschaften, Mitglieder, Sponsoren, Vereine) kommen mit Bricks2Etch direkt mit.
- Etch unterstützt Meta Box offiziell (`this.metabox.*`).
- **Regel für Verknüpfungen, nach denen gefiltert wird:** als einfaches Feld mit einer Post-ID speichern (z. B. Spiel → `team_id`), nicht über MB Relationships. MB Relationships liegen in einer eigenen Tabelle und brauchen in Etch Sonder-Loops (`mbFrom`/`mbTo`); ein einfaches Feld geht mit `meta_query` und `$team: this.id`.
- ACF Pro wäre die Alternative mit dem breitesten Etch-Adapter, kostet aber Lizenz und eine zusätzliche Umstellung der Altdaten.

### Bestand der alten Seite (öffentliche REST-API, 21.10.2026)

- **Technik:** WordPress auf IONOS, Theme Bricks + Child, AutomaticCSS, Frames, Meta Box (Builder, Relationships, Views, Blocks), Fluent Forms, SEOPress, Wordfence, Pressidium Cookie Consent, HappyFiles.
- **Inhalte:** 178 Beiträge (seit 2013), 10 Seiten, 482 Medien.
- **Eigene Inhaltstypen:** `mannschaften` (11), `mitglieder` (131, Taxonomien Position und Vorstandsrolle), `sponsor` (70, Taxonomie Partner-Stufe), `vereine` (128 – Gegner mit Wappen, wichtig gegen Hotlinking), `historie` (5), `foerderverein` (4), `rechtlich` (2).
- **Mannschaften heute:** 1. Mannschaft, 2. und 3. Mannschaft SG Gem. B.K.N., AH, A-, B-, C- (SG), D-, E- (SG, 2 Teams), Schiedsrichter. **Abweichung zu den Entwürfen:** Dort steht Ü35 statt AH, es gibt keine 3. Mannschaft, und Mädchen, Bambini und F-Junioren fehlen auf der alten Seite. Das mit dem Verein klären.
- **Sponsoren:** Stufen wie im Entwurf (Sportpark, Trikot 1. Mannschaft, Trikot SG, Exklusiv 3, Premium 3, Präsentationsanzüge, Bande-Plus 7, Bande 49, Basis 5).
### SSH-Inventur (21.10.2026, nur gelesen)

- **System:** WordPress 7.1.3, Bricks 2.4.3 + Child, WP-CLI vorhanden, Uploads 620 MB (einzelne Originalfotos 16–17 MB → bei der Migration verkleinern).
- **Aktive Plugins:** Admin Columns Pro, AutomaticCSS 3.3.8, Frames, Fluent Forms Pro, HappyFiles Pro, Meta Box AIO, SEOPress Pro, Wordfence, WPvivid Backup Pro, Pressidium Cookie Consent, Maileroo SMTP, GDPR-YouTube, Remove CPT Base, IONOS-Plugins, **WP File Manager** (Sicherheitsrisiko, sollte auf der neuen Seite nicht mehr drauf).
- **Schema per Code:** mu-plugin `fck-club-meta-box` lädt alle Feldgruppen aus JSON (Quelle: Repo `wordpress-club-meta-box`). CPTs und Taxonomien sind in Meta Box angelegt (7 CPTs, 3 Taxonomien, 1 Relationship), dazu 8 MB Views und 7 Bricks-Templates (Header, Footer, Blog, Mannschaft, Historie).
- **Feldgruppen heute:**
  - *Mannschaften:* Teamschlüssel, fussball.de-ID, Liga, Kategorie, Schalter „Spielplan anzeigen“ + die serialisierten Sync-Felder (letzte Spiele, nächste Spiele, Tabelle).
  - *Mitglieder:* Vorname, Nachname, Spieler ja/nein, Foto, Position; Vorstand ja/nein, Vorstandsfoto, Rolle, Adresse, E-Mail, Telefon. **Keine Rückennummer, kein Einwilligungsfeld.**
  - *Sponsoren:* Logo, Website, Stufe. *Vereine (Gegner):* Platzadresse (+ Beitragsbild als Wappen).
  - *Club-Einstellungen:* Logo, Adresse, Vereinsheim, Social-Links und ein **zweiter, manueller Spielplan** (18 Spiele, mit Gegnern aus „Vereine“, abgesagt, Ergebnis).
- **Daten:** 120 Spieler (fast alle mit Foto), 13 Vorstandsmitglieder. Die Relationship „Mannschaft ↔ Spieler“ ist **leer**: Spieler sind keinem Team zugeordnet → beim Umzug zuordnen.
- **Pipeline läuft:** 10 von 11 Teams haben eine fussball.de-ID und wurden heute um 03:00 synchronisiert. Die D-Junioren hängen seit 30.06. (vermutlich alte Team-ID der Vorsaison). Schiedsrichter haben keine ID.
- **Kategorien:** Aktive 73, Allgemein 57, Nachwuchs 49, Spielberichte 38, Veranstaltungen 20, Kunstrasen 10, Neubau 7, AH 5, Förderverein 5, Bezirkspokal 4, Weihnachten 3, Verbandspokal 2, Gemeindepokal 1.
- **Datenschutz-Hinweis:** Viele Spielerfotos ohne dokumentierte Einwilligung, vermutlich auch von Jugendlichen. Vor dem Umzug klären, welche Fotos übernommen werden.

**Was das für den Plan heißt:** Das bestehende Meta-Box-Schema ist die Basis. Wir erweitern es (Spiel als eigener CPT, Rückennummer, Einwilligung, Team-Zuordnung, Trainingszeiten, Spielstätte) statt neu anzufangen. Der manuelle Spielplan aus den Club-Einstellungen wird durch den CPT „Spiel“ mit `quelle=manuell` ersetzt.

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
3. **Wie:** Neue Staging-Seite mit Etch, dann **Bricks2Etch**: überträgt Beiträge, Seiten, Medien, Beitragsbilder, CPTs, Meta-Box-Feldgruppen und Feldwerte, Fonts und globale Klassen. Danach alte Kategorien auf die neuen abbilden und `mitglieder` → Person, `vereine` → Gegner-Wappen nutzen. Bricks-Templates werden nicht gebraucht, die Seiten entstehen neu nach den Paper-Entwürfen.
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
| 0 | SSH-Inventur der alten Seite, Teamliste klären, Rechtsfrage fussball.de anstoßen | Bestandsliste |
| 1 | Staging-Seite mit Etch, Bricks2Etch-Migration der Inhalte, CPTs in Etch, Felder in Meta Box, `fck-core`, ACSS-Tokens aus den Entwürfen | Datenmodell mit echten Altdaten |
| 2 | Etch: Header, Footer, Komponenten (Spielkarte, Tabelle, Kachel, Partner-Zeile, Person), dann die Templates | alle Seiten mit Testdaten |
| 3 | Pipeline umbauen und an `fck/v1/sync` anschließen, Rechenwerte in `fck-core` | echte Spieldaten |
| 4 | Inhalte aufräumen und ergänzen (Einwilligungen, Logos als SVG), Weiterleitungen | Inhalte fertig |
| 5 | Teams schulen (Trainer-Rolle), Abnahme, Umzug auf die Live-Domain | live |

---

## 11. Offene Punkte

- Teamliste mit dem Verein abgleichen (AH/Ü35, 3. Mannschaft, Mädchen, Bambini, F-Junioren, Schiedsrichter).
- Erlaubnis für das Auslesen von fussball.de.
- Liefert fussball.de Spieler-Saisonzahlen überhaupt? Sonst manuell.
- Echte Inhalte vom Verein: Logos (SVG), Fotos, Namen von Jugendleitung und Vertrauensperson, Trainingszeiten, Adressen Neuhausen/Buchenberg, Satzung und Kinderschutzkonzept, Sponsorenmappe.
