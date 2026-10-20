# FC Königsfeld · Konzept fürs Homepage-Redesign

Stand: 20.10.2026. Grundlage sind:
- Persona-Review der Homepage V2 mit 6 Personas plus UI/UX- und Barrierefreiheits-Check
- Benchmark von 12 Profi- und 12 Amateurvereins-Websites
- Recherche auf fussball.de und fc-koenigsfeld.de
- Analyse von [`fussball.de-2-wordpress`](https://github.com/tobiashaas/fussball.de-2-wordpress)

Ziel: **die beste Erfahrung für alle Personas.** Das sind:
- Fan
- Eltern, Jugend und Neumitglieder
- Spieler und Trainer
- Sponsoren
- Verband, Presse und Gastvereine
- Vorstand und Redaktion

Danach folgt der Nachbau im Design-Tool, dann Etch + WordPress + ACSS.

---

> **Typografie-Entscheidung (20.10.2026):** Keine Monospace-Schrift mehr. Geist Mono fällt komplett weg. Kicker, Labels, Daten, Ergebnisse und Tabellen laufen in **Geist**: Versal-Labels in 600 mit 0,08 em Laufweite, Zahlen mit `tabular-nums`. Überschriften bleiben Basement Grotesque.

## 1. Grundsätze

1. **Nichts Handgepflegtes, das veraltet.** Spieldaten kommen aus der Pipeline. News, Termine, Partner und Rollen kommen aus WordPress-Inhalten mit Ablaufdatum. Jede Komponente hat einen **Leerzustand**.
2. **Für alle, nicht nur für die Erste.** Jugend, Mädchen und alle Herrenteams sind auf der Startseite sichtbar. Die Team-Liste ist datengetrieben und jederzeit erweiterbar.
3. **Jede Persona findet ihren Einstieg in 10 Sekunden.** Dafür gibt es ein Einstiegsband unter dem Hero und klare Hauptmenüpunkte.
4. **Kompakt statt gestapelt.** Spiel, Ergebnis und Tabelle bilden *ein* Spieltags-Modul. Das spart auf Mobil etwa 1.500 px.
5. **Klickbar heißt klickbar.** Hover-Lift nur auf Karten, die verlinkt sind. Kein Slider, kein „Mehr“-Sammelmenü.
6. **Datenschutz von Anfang an.**
   - Keine Drittinhalte ohne Einwilligung (Maps nur als Link, fussball.de-Widget nur als Rückfallebene mit Zwei-Klick-Lösung).
   - Personenfotos nur mit Einwilligung. Bei Minderjährigen standardmäßig Teamfoto statt Einzelfoto.
7. **WCAG 2.2 AA** als Untergrenze. Farbe ist nie der einzige Informationsträger.

---

## 2. Gesicherte Fakten (fussball.de / fc-koenigsfeld.de, abgerufen 20.10.2026)

| Mannschaft (offiziell) | Spielklasse 2026/27 | Heimplatz |
|---|---|---|
| Herren · FC Königsfeld | **Landesliga 3** (Südbaden), SBFV-Pokal | HSS Hydraulik Sportpark (Kunstrasen + Rasen) |
| Herren · SG Gemeinde Königsfeld B.K.N. | Kreisliga B, Staffel 1 | Sportplatz Neuhausen |
| Herren · SG Gemeinde Königsfeld B.K.N. 2 | Kreisliga C, Staffel 1 | Sportplatz Neuhausen |
| Herren Ü35 · FC Königsfeld | nur Bezirkspokal | – |
| A-Junioren · SG Gemeinde Königsfeld | Bezirksliga | HSS Sportpark |
| B-Junioren · SG Gemeinde Königsfeld | Kreisliga 1 | Sportplatz Neuhausen |
| C-Junioren · SG Gemeinde Königsfeld | Kreisliga 1 | Sportplatz Buchenberg |
| D-Junioren · SG Gemeinde Königsfeld | Bezirksliga, Staffel 8 (Herbst) | HSS Sportpark |
| E-Junioren · SG Gemeinde Königsfeld (+ Team 2) | Herbstrunde (Bezirksturnier) | – |
| F-Junioren, Bambini, Mädchen | **nicht auf fussball.de** → Daten von Hand pflegen | – |

- **fussball.de-Vereins-ID:** `00ES8GN9DO000062VV0AG08LVUPGND5I`
- **Spielgemeinschaft:** Die Jugend spielt komplett als SG mit Buchenberg und Neuhausen („B.K.N.“). Das muss auf der Seite erklärt werden, sonst wundern sich Eltern.
- **Adressen:**
  - Sportpark und Vereinsheim: Schwimmbad 3, 78126 Königsfeld
  - Sportplatz Neuhausen: Forststr. 86
  - Sportplatz Buchenberg: Beerenreute 2
- **Kontakt:** Funktionsadressen für Vorstand, Jugendleitung und Spielausschuss existieren (vorstand@, jugendleiter@, spielausschuss@).
- **Social Media:** Instagram `@fckoenigsfeld1954`, Facebook `FC.Koenigsfeld`, YouTube `@FCKonigsfeldeV`.
- **Beiträge** (ab 01.01.2025): Erwachsene 80 €, Jugendliche bis 18 Jahre 70 €, Familien 120 €. Einzug am 1. März.
- **Joshua Kimmich Junior Cup:** offizieller Name, darf verwendet werden.
- **Saison 2025/26:** Herren 1. Platz in der Landesliga, danach Aufstiegsspiel zur Verbandsliga; A-Junioren 1. Platz Kreisliga (laut fussball.de-Vereinserfolgen).

**Noch zu klären (Verein):**
- Mädchenteam: eigenes Team oder SG? In welcher Runde?
- F-Jugend und Bambini: Trainingszeiten und Ansprechpersonen
- Trainingszeiten aller Teams (stehen nirgends)
- Vorstandsseite (aktuell leer)
- Registergericht und VR-Nummer
- Kinderschutz-Vertrauensperson
- Reichweitenzahlen für Sponsoren (Mitglieder, Zuschauer, Follower)

---

## 3. Startseite · neue Struktur

Die Spalte „Leerzustand“ ist Pflicht für das Design: Jede Section muss in Paper in beiden Zuständen gestaltet werden.

| # | Section | Für wen | Inhalt | Datenquelle | Leerzustand |
|---|---|---|---|---|---|
| 0 | **Header** | alle | Wappen, 6 Hauptpunkte, CTA „Mitglied werden“ | WP-Menü | – |
| 1 | **Hero, kompakt** | alle | H1, ein Satz, 2 CTAs („Spielplan“, „Mitmachen“). Auf Mobil max. ~70 vh, ohne Rail | ACF-Optionen | – |
| 2 | **Persona-Einstiegsband** | alle | 4 Kacheln: *Kind anmelden / Probetraining* · *Heimspiel & Anfahrt* · *Mitglied werden* · *Partner werden* | statisch | – |
| 3 | **Spieltag-Modul** | Fan, Spieler, Eltern | Team-Tabs (Herren 1 · Herren 2/3 · Jugend A–F · Mädchen · Ü35). Je Tab: nächstes Spiel + letztes Ergebnis + Tabellenausschnitt (±2) + Formkurve. Darunter „Alle Spiele dieses Wochenende“ | Pipeline | Sommerpause: „Saisonstart am …“ · kein Termin: „Nächster Termin folgt“ + Link Spielplan · Sync-Fehler: letzter Stand + „Stand: …“ |
| 4 | **News** | Fan, Presse | Aufmacher + 3 Beiträge, Kategorie-Badges sind verlinkt | WP-Beiträge | „Noch keine Beiträge“ ist unrealistisch, der Aufmacher fällt aufs Wappen zurück |
| 5 | **Jugend & Mädchen** | Eltern, Kinder | „Dein Kind will kicken?“. Jahrgangs-Chips (aus der Saison berechnet), Mädchen-Karte, Probetraining-CTA, Ansprechperson Jugendleitung, Link Kinderschutz, Erklärung SG B.K.N. | CPT Mannschaft + Ansprechperson | – |
| 6 | **Termine** | Fan, Familien | nächste 3 Events (vergangene verschwinden automatisch) + „Kalender abonnieren“ (ICS) | CPT Event | Section ausblenden oder „Gerade nichts geplant, folge uns auf Instagram“ |
| 7 | **Mitmachen, 3 Wege** | Neumitglied, Ehrenamt | *Spielen* (Probetraining) · *Helfen* (aktive Ehrenamt-Rollen, z. B. „Trainer:in E-Jugend gesucht“) · *Unterstützen* (Beiträge 80/70/120 €, Förderverein) | CPT Rolle + ACF | Bei „Helfen“ ohne offene Rolle: allgemeiner Aufruf |
| 8 | **Teams-Übersicht** (ersetzt den Kader-Teaser) | Spieler, Eltern | Raster aller Teams mit Spielklasse und Trainingstagen. Spieler-Teaser nur Aktive, nur mit Einwilligung | CPT Mannschaft / Spieler | – |
| 9 | **Partner** | Sponsoren | Namensgeber HSS Sportpark · Exklusiv-Partner · Logoleiste weiterer Partner. Alle Logos verlinkt (`rel="sponsored"`). Streifen „Partner werden“ mit 3–4 Kennzahlen + Ansprechperson | CPT Sponsor (Stufe, Laufzeit) | leere Stufe wird ausgeblendet |
| 10 | **Footer** | Verband, Presse, Gäste | Kontakt (Verein, Vereinsheim), 4 Spielstätten mit Maps-Link, Social (IG/FB/YT), Schnelllinks: Presse · Für Gastvereine · Kinderschutz · Downloads, Impressum/Datenschutz | ACF-Optionen + CPT Spielstätte | – |

**Bleibt aus V2:**
- Marke: Basement Grotesque, FCK-Rot, Diagonale, Kicker-System
- die überlappende Spielkarte als Kern des Spieltag-Moduls
- Tabelle mit hervorgehobener eigener Zeile
- Formkurve mit Buchstaben
- Aufmacher-Layout der News
- Event-Kacheln mit großem Datum
- Sponsoren-Stufen

### Regeln für das Spieltag-Modul

- **Nächstes Spiel:**
  - Badge **Heim** oder **Auswärts**, dazu Wettbewerb und Spieltag
  - Datum mit Wochentag und Jahr, *aus dem Datum berechnet*
  - Anstoß, oder „Anstoß folgt“
  - Spielstätte + Adresse + Link „Route“
  - Button „In Kalender“, Link Vorbericht
  - Trenner „vs.“, nicht „:“
- **Letztes Ergebnis:**
  - aus FCK-Sicht, mit Badge **S/U/N** (Text plus Farbe)
  - Ergebnis mittig zwischen den Teams
  - Link zum Spielbericht (über `fd_spiel_id`)
- **Status:**
  - Status-Badges *verlegt*, *abgesagt*, *Wertung*
  - Live-Zustand erst, wenn die Pipeline ihn liefern kann
- **Tabelle:**
  - Zonen für Aufstieg, Relegation und Abstieg in eigenen Farben, mit Legende
  - Desktop: `Sp · S · U · N · Tore · Diff · Pkt`
  - Mobil: `Sp · Diff · Pkt`
- **Formkurve:** Leserichtung „älter → neuer“ beschriften, nur Ligaspiele zählen.
- **F-Jugend, Bambini, Mädchen:** Diese Teams haben keine Tabelle (sie spielen Spielfeste bzw. Turniere). Der Tab zeigt dort nur Termine.

---

## 4. Navigation

Sechs Hauptpunkte. Jeder Punkt hat ein Mega-Panel auf Desktop und ein Akkordeon im mobilen Menü.

- **Teams:** Herren (1 · SG 2 · SG 3 · Ü35) · Jugend (A–F, Bambini) · Mädchen · Trainer:innen · *Feature: „Dein Kind will kicken?“*
- **Spielbetrieb:** Spieltag · Spielpläne · Tabellen · Heimspiel & Anfahrt · Für Gastvereine · Spielstätten
- **Aktuelles:** News (nach Kategorien) · Termine · Fotos · Kalender-Abo
- **Verein:** Über uns · Vorstand & Kontakt · Historie · Sportpark & Vereinsheim · Förderverein · Kinderschutz · Presse · Downloads
- **Mitmachen:** Mitglied werden · Probetraining · Ehrenamt · Trainer:in werden
- **Partner:** Unsere Partner · Partner werden · Werbeflächen & Mediadaten

**Regeln:**
- Eine Bezeichnung pro Sache (nicht „Medien/Blog/Aktuelles“ gemischt).
- Keine zwei Menüpunkte mit demselben Ziel.
- Teams im Menü kommen aus dem CPT, nicht aus `navigation.js`.

**Mobiles Menü:**
- eigener Schließen-Button
- Akkordeons
- CTA „Mitglied werden“ unten in der Daumenzone
- Schnellzeile „Spieltag · Tabelle · Probetraining“
- `role="dialog"`, Fokus-Trap, `inert` für die Seite dahinter

### Navigation nach Vorbild Crystal Palace (cpfc.co.uk, gesichtet 20.10.2026)

**Desktop-Header:**
- Großes Wappen links, es ragt über die Leiste.
- 4 Hauptlinks als **schräg geschnittene Segmente**. Das passt zur FCK-Diagonale (110°) aus dem Hero.
- Rechts ein **rot abgesetztes Schrägsegment** für die Hauptaktion: bei CPFC „Login“, beim FCK „Mitglied werden“.
- Burger mit „Menü“.

**Mobil-Header, zweistufig:**
- Oben eine Utility-Leiste: *Spielplan · Tabelle · rotes Segment „Mitglied werden“*.
- Darunter die Hauptleiste: Wappen, 2–3 sichtbare Top-Links (*Spieltag · Teams · News*) und Burger.
- Die wichtigsten Aufgaben sind damit auch mobil mit einem Tipp erreichbar, statt im Burger zu verschwinden.

**Menü = Vollbild-Overlay**, gleich auf Desktop und Mobil. Es ersetzt Mega-Menü *und* Drawer:
- FCK-roter Grund mit Wappen als großem Wasserzeichen
- links die 6 Hauptpunkte in **riesiger Basement Grotesque** (Spieltag · Teams · Aktuelles · Verein · Mitmachen · Partner)
- rechts (mobil darunter) gruppierte Sekundärlinks:
  - *Dein Besuch:* Heimspiel & Anfahrt · Für Gastvereine · Vereinsheim
  - *Jugend & Mädchen:* Für Eltern · Probetraining · Mädchen · Kinderschutz
  - *Service:* Kontakt & Vorstand · Presse · Downloads · Kalender-Abo
- dazu Social-Icons
- unten ein fixiertes Schrägsegment als CTA „Mitglied werden“
- technisch ein Dialog mit Fokus-Falle; die Logik aus dem aktuellen Drawer lässt sich übernehmen

**Spieltags-Widget im Hero**, rechts auf Desktop, mobil direkt unter dem Hero:
- schräge Team-Tabs (Herren · Jugend · Mädchen · Ü35)
- Karte *Letztes Spiel*: Ergebnis mit Text-Pille SIEG/NIEDERLAGE/REMIS zwischen den Toren, dazu 2 Aktionen (Spielbericht · Spielplan)
- Karte *Nächstes Spiel*: Anstoß groß, Aktionen Anfahrt · In Kalender
- Mini-Tabelle ±1 um das eigene Team, mit Trendpfeilen
- Das ersetzt die eigene „Nächstes Spiel“-Leiste und rückt das Spieltags-Modul in den ersten Bildschirm.

**Nicht übernehmen:**
- Hero-Slider (Pflegeaufwand)
- Versalien überall
- graue Datumsangabe auf Blau mit zu wenig Kontrast
- 9 gleich große Overlay-Links ohne Gruppierung (beim FCK bleibt es bei 6)
- Shop, Login, Sprachwahl

### Hover „Rolling Text“ (Vorbild motion.dev)

Jeder Navi-Link (und optional jeder Button) enthält sein Label zweimal. Beim Hover und Fokus rollt die sichtbare Kopie nach unten raus, die zweite rollt von oben rein: `translateY` über ca. 0,38 s, `cubic-bezier(.22,1,.36,1)`. Das ist reines CSS und braucht kein JS.
- Für Screenreader trägt der Link ein `aria-label`, die beiden Kopien sind `aria-hidden`.
- Bei `prefers-reduced-motion` gibt es keine Animation.

Zusätzlich von motion.dev: Mobil bleiben die Hauptlinks als kompakte Zeile sichtbar, statt im Burger zu verschwinden. Das deckt sich mit dem zweistufigen Mobil-Header oben.

**Prototyp:** `prototypes/rasen-shader.html` (Header mit Schrägsegmenten und Rolling Text).

### Hintergrund „Rasen-Shader“ (Vorbild native.security)

native.security nutzt eine three.js-WebGL-Szene: Rauschen wird in Stufen quantisiert (Höhenlinien mit hellem Rand), darüber liegt animiertes Korn. Die Szene bewegt sich langsam mit Zeit und Scroll.

Unsere Variante ist ein **reiner WebGL2-Fragment-Shader ohne Bibliothek** (wenige KB statt three.js plus 550-KB-Heldenfoto):
- Mähstreifen, wellig verzogen entlang der Höhenstufen, die an den Schwarzwald erinnern
- Korn als Grashalme
- Mittelkreis und Mittellinie in Kreide, dezent
- Flutlicht-Abfall von oben rechts
- Abdunklung links für die Lesbarkeit der Headline

Varianten: `?mode=nacht | tag | rot`.

**Empfehlung:**
- **„Flutlicht“ (nacht)** für den Hero. Bestes Kontrastverhältnis, und Rot bleibt Akzent.
- **„FCK-Rot“** für das Overlay-Menü bzw. das Mitmachen-Band, dort nur mit weißem Text. Rotes „1954“ auf Rot ist zu schwach.
- **„Matchday“ (grün)** nicht verwenden. Grün und Rot beißen sich, und das Grün dominiert die Marke.

**Technische Regeln:**
- pausiert außerhalb des Viewports (IntersectionObserver)
- bei `prefers-reduced-motion` ein Standbild
- DPR auf 1,5 begrenzt
- CSS-Streifen als Fallback ohne WebGL
- `aria-hidden` auf dem Canvas
- In Etch als kleines Script plus `<canvas>` in der Hero-Komponente; der Modus wird pro Section als Attribut gesetzt.

### Bento-Grids (Wunsch des Nutzers)

Bento ersetzt mehrere gestapelte Bänder durch ein Raster mit unterschiedlich großen Kacheln. Jede Kachel ist ein Persona-Einstieg mit genau einer Aktion. Das verkürzt die Seite und bricht die Gleichförmigkeit auf. Einsatz an zwei Stellen:

1. **„Dein FCK“-Bento** unter dem Hero mit Spieltags-Widget. Es ersetzt das Einstiegsband, die Sections *Jugend & Mädchen* und *Mitmachen* sowie den Kader-Teaser.

   | Kachel | Größe | Inhalt |
   |---|---|---|
   | Jugend & Mädchen | groß, 2×2 | Teamfoto, Jahrgangs-Chips, „Probetraining vereinbaren“ |
   | Probetraining | 1×1 | rote Kachel, „Einfach vorbeikommen“ |
   | Heimspiel & Anfahrt | 2×1 | nächstes Heimspiel + Adresse + „Route“ |
   | Nächster Termin | 1×1 | Event mit großem Datum, „Alle Termine“ + ICS |
   | Mitglied werden | 1×1 | „ab 70 € im Jahr“ (Beiträge aus ACF) |
   | Ehrenamt | 1×1 | aktuell offene Rolle („Trainer:in E-Jugend gesucht“) |
   | Teams | 2×1 | Raster aller Teams mit Liga, Link zur Teamseite |
   | Instagram | 1×1 | Handle `@fckoenigsfeld1954`, Link ohne Einbettung (keine Einwilligung nötig) |
   | Partner werden | 1×1 | Kennzahl + Ansprechperson |

2. **News-Bento:** Aufmacher 2×2, zwei Beiträge 1×1, ein Spielbericht 2×1.

**Regeln:**
- Feste Kachelgrößen (1×1, 2×1, 2×2) auf einem 4-Spalten-Raster (Desktop), 2 Spalten (Tablet), 1 Spalte (Mobil).
- Mobil bleiben die wichtigsten Kacheln oben: Jugend → Probetraining → Heimspiel.
- Jede Kachel ist komplett klickbar und hat einen Fokus-Ring. Rot gibt es pro Raster nur in *einer* Kachel.
- Jede Kachel hat einen Leerzustand. Ohne offene Ehrenamt-Rolle erscheint ein allgemeiner Aufruf, ohne Termin die Kalender-Abo-Kachel.
- In Etch ist das CSS-Grid mit `grid-template-areas`. In Paper wird es mit Flex-Zeilen nachgebaut.

**Unterseiten, die das Konzept voraussetzt (noch nicht vorhanden):**
- Heimspiel & Anfahrt
- Für Gastvereine
- Jugend für Eltern
- Mädchen
- Probetraining
- Kinderschutz
- Ehrenamt
- Partner werden (Pakete, Preise, Mediadaten-PDF, Ansprechperson)
- Presse (Logo-Paket, Ansprechperson)
- Vorstand & Kontakt

---

## 5. Datenmodell (WordPress) und Pipeline

**Inhaltstypen (CPTs):**
- **Mannschaft:** Name, Kurzname, Altersklasse (Herren/A–G/Mädchen/Ü35), SG ja/nein, Liga, Staffel, Saison, `fd_team_id` (optional), Heimplatz → Spielstätte, Trainingszeiten (Repeater), Ansprechperson(en), Sortierung, „auf Startseite“, Zonen (Aufstieg/Relegation/Abstieg), Sync-Status, zuletzt aktualisiert
- **Spiel:** ein Datensatz pro Spiel. Felder: `fd_spiel_id`, Mannschaft, Datum/Zeit (ISO), Anstoß offen, Spieltag, Wettbewerb, heim (bool), Gegner + Wappen, Spielstätte, Tore eigen/Gegner (int), Ergänzung (n.V./i.E./Wertung), Status (geplant/live/beendet/verlegt/abgesagt), Spielbericht-Beitrag, Quelle
- **Tabelle:** Snapshot je Mannschaft (Platz, Team, Sp, S, U, N, Tore ±, Diff, Pkt, eigenes Team, Zone)
- **Event:** Start/Ende, ganztägig, Ort, Kurztext, Link, Kategorie (Jugend/Familie/Verein)
- **Spielstätte:** Name, Adresse, Koordinaten, Platzart, Parken, Hinweise für Gäste
- **Sponsor:** Stufe, Logo, URL, Laufzeit-Ende (danach wird er automatisch ausgeblendet)
- **Ehrenamt-Rolle:** Titel, Aufwand, Kontakt, aktiv
- **Ansprechperson:** Funktion, Funktionsmail, Foto (Einwilligung)
- **Spieler:** nur Aktive. Einwilligung Foto/Name, minderjährig, Einwilligungsdatum. Die Abfrage zeigt nur Einträge mit gültiger Einwilligung.

**Globale Optionen:** aktuelle Saison, Zeitraum der Sommerpause, aktuelle Liga der 1. Mannschaft (für Hero, Tabelle, Menü), Social-Links, Beiträge, Kennzahlen für Sponsoren.

**Serverseitig berechnet, nicht im Template:**
- Formkurve
- nächstes und letztes Spiel je Team
- Tabellenausschnitt ±2
- Jahrgänge je Altersklasse
- Leerzustand (`sommerpause` / `kein_termin` / `keine_daten` / `sync_fehler`)

### Gaps in `fussball.de-2-wordpress` (Stand heute)

Das Repo enthält bisher nur Konzept, Meta-Box-Feldgruppen und ein Hilfsskript. Die Logik liegt im **n8n-Workflow, der nicht versioniert ist**. Die Daten landen als serialisierte Clone-Gruppen im Team-Postmeta. Damit lassen sie sich in Etch kaum abfragen oder sortieren („nächstes Spiel über alle Teams“).

**Fehlt für dieses Konzept:**
- Heim-Flag
- Spielstätte
- Spieltag
- Status (verlegt/abgesagt)
- Spiel-ID (für die Verknüpfung mit dem Bericht)
- Tore als Zahlen
- Zonen
- Formkurve
- Saison- bzw. Pausen-Flag
- Unterscheidung „leer“ vs. „Sync fehlgeschlagen“
- Alerting

**Empfehlungen:**
1. Workflow-JSON ins Repo aufnehmen.
2. Auf das Datenmodell oben umstellen: Spiele als eigene Datensätze.
3. Am Wochenende öfter synchronisieren (heute 1×/Tag um 03:00).
4. „Last known good“ behalten statt bei Fehlern zu überschreiben, plus Alert.
5. Das statische Font-Mapping für die Ergebnis-Verschleierung absichern (Prüfung + Alert). Im Hilfsskript ist ein Bug: `"hyphen": ":"`.
6. Für den Sync einen eigenen WordPress-Benutzer mit minimalen Rechten anlegen.
7. **Prüfen:** Was sagen die Nutzungsbedingungen von fussball.de zum automatisierten Auslesen? Der Hinweis aus der Analyse ist *nicht verifiziert*. Ggf. mit dem SBFV klären. Der offizielle Rückfallweg sind die fussball.de-Widgets mit Zwei-Klick-Einwilligung.
8. Bei Jugendspielen keine Namen von Torschützen übernehmen.

---

## 6. Design-System-Korrekturen (vor bzw. beim Nachbau)

| Token / Element | Ist | Soll |
|---|---|---|
| Fokus-Ring `#ffc10a` | 1,6:1 auf Weiß | doppelter Ring (dunkel + weißer Innenrand), `:focus-within` an Karten |
| Sieg-Grün `#1f8a3d` | 4,41:1 | `#156b2c` (6,6:1) |
| Unentschieden `#b38600` (Pille) | 3,3:1 | `#8a6700` (5,2:1) |
| Spaltentitel im Mega-Menü `#a6a6a6` | 2,4:1 | ≥ `#767676` |
| `--text-xs`, `--h6`, `--space-xs` | `clamp()` vertauscht (min > max) | in Paper sauber neu definieren |
| Hero-Bild | 552 KB, 2560 px, ohne `srcset` | AVIF/WebP + `srcset`, `alt=""` (Stockfoto) |
| Hero-H1 | per JS ausgeblendet, bis Motion lädt (schlecht für LCP) | Hero nicht per JS animieren |
| Basement Grotesque | kein Preload, kein Fallback mit angepassten Metriken | `preload` + Fallback mit `size-adjust` |

**Sofort im Code behebbar** (unabhängig vom Redesign):
- Schließen-Button im mobilen Menü (verdeckt, z-index)
- `<main>` + Skip-Link
- Kontraste aus der Tabelle oben
- „Exclusiv“ → „Exklusiv“
- `Sponsoren.html#mitglied` → eigenes Ziel
- echter Instagram-Link
- Hover-Lift nur auf verlinkten Karten
- „Mehr“-Button der Events ist auf Mobil ausgeblendet → ganze Karte verlinken

---

## 7. Anti-Muster (aus dem Benchmark)

- Hero-Slider und Karussells: brauchen ständig neuen Inhalt
- Live-Widgets ohne Fallback (Brighton zeigt „undefined – undefined“)
- Startseiten mit 15+ Sections und Werbebannern dazwischen
- Sammelmenü „Mehr“
- News ohne Datum, Tagesmeldungen, die stehen bleiben
- Inhalte nur als PDF (Beitrittsformular von 2019)
- Formulare, die ohne Cookie-Einwilligung nicht funktionieren
- Jugendseiten ohne Probetraining und Kinderschutz
- Sponsoring nur als Logoleiste ohne Angebot

## 8. Ideen mit gutem Aufwand-Nutzen-Verhältnis

- **ICS-Kalender-Abo pro Team:** größter Nutzen für Eltern.
- **WhatsApp-Kanal oder Newsletter**, z. B. „Spieltag am Wochenende“.
- **Probetraining ohne Formular-Hürde:** „bis zu 3× kostenlos, einfach vorbeikommen“, dazu eine Mitbringliste.
- **Jahrgänge automatisch berechnen.** Nur die Trainingszeiten werden 1×/Saison gepflegt.
- **Sponsoring mit Preisen + Ansprechperson.** Die meisten Vereine machen nur eins von beidem.
- **Anfahrt als Maps-Link** statt Einbettung, dann ist keine Einwilligung nötig.

## Quellen (Auswahl)

- **Profivereine:**
  - [Brentford](https://www.brentfordfc.com/en) (Team-Tabs)
  - [Holstein Kiel Anreise](https://www.holstein-kiel.de/stadionerlebnis/anreise-und-parken/) · [Partner werden](https://www.holstein-kiel.de/partner/partner-werden/)
  - [Heidenheim Gästefans](https://www.fc-heidenheim.de/infos-fuer-gaestefans)
  - [SV98 Kalender](https://www.sv98.de/kalender)
  - [St. Pauli Mitgliedschaft](https://www.fcstpauli.com/verein/mitgliedschaft/fc-st-pauli-mitgliedschaft)
- **Amateur- und Regionalvereine:**
  - [SG Walluf](https://sgwalluf1932.de/) · [SG Walluf Jugend](https://sgwalluf1932.de/jugend/)
  - [Lewes FC](https://lewesfc.com/)
  - [DJK Donaueschingen Schutzkonzept](https://www.djk-donaueschingen.de/praeventionsschutzkonzept)
  - [Clapton CFC Ehrenamt](https://www.claptoncfc.co.uk/volunteer-with-clapton-cfc/)
  - [SV Kressbronn Sponsoring](https://svkressbronn.de/sponsoring/)
  - [FC 08 Villingen Anreise](https://fcvillingen.de/stadion/anreise-parken/)
  - [BFV-Kinderschutzkonzept](https://www.badfv.de/fileadmin/Dateien/Downloads/Kinderschutz/Kinderschutzkonzept_190226.pdf)
- **Daten:**
  - [fussball.de Vereinsseite](https://www.fussball.de/verein/fc-koenigsfeld-suedbaden/-/id/00ES8GN9DO000062VV0AG08LVUPGND5I)
  - [fc-koenigsfeld.de](https://fc-koenigsfeld.de/)
