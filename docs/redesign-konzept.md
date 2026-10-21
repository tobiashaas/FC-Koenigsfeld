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

> **Hintergrund-Entscheidung (20.10.2026):** Die ganze Seite liegt auf **dunklem Grund (#121212) mit feinem Filmkorn** und leichtem Flutlicht-Schein von oben. Es gibt keine Höhenlinien und keinen Rasen. Spielfeld-Markierungen (weiß, ca. 14 % Deckkraft) erscheinen nur, wo sie Sinn ergeben, und die Seite liest sich wie ein Spielfeld von oben nach unten:
> - Eckfahnen-Bögen in den oberen Ecken unter dem Hero
> - Mittellinie, Mittelkreis und Mittelpunkt in der Lücke zwischen den Sections
> - Strafraum mit Torraum, Elfmeterpunkt und Bogen direkt vor dem Footer; die Footer-Kante ist die Torlinie
>
> **Update 21.10.2026 (Konsistenz):** Korn gibt es **nur auf dem dunklen Grund**. Rot ist überall eine **flache Markenfläche ohne Korn**: Hero-Diagonale, Akzentkachel, Buttons, Menü und Footer-Karte. Grund dafür: Rot mit Korn im Footer und flaches Rot oben wirkten wie zwei verschiedene Farben. Das Korn ist eine nahtlose 600-px-Kachel ohne Lichtschein (`prototypes/bg-korn-kachel.png`, erzeugt mit `scripts/korn-kachel.py`). Sie wird auf Body, Sections und Footer identisch gekachelt, damit es an den Übergängen keine Kanten gibt (im Code: `background: #121212 url(korn.png) repeat` am `body`, Sections transparent). Der frühere Flutlicht-Schein entfällt, weil er sich in jedem Abschnitt wiederholte. Die Markierungen sind SVG.

> **Tabelle (20.10.2026):** Die Mini-Tabelle ist aus dem Hero raus. Der Spieltags-Block im Hero zeigt nur noch letztes und nächstes Spiel mit Team-Tabs. Die Tabelle hat jetzt eine eigene Section **„Wo wir stehen.“** direkt unter „Dein FCK“:
> - links die Tabellenkarte mit `# · Verein · Sp · S · U · N · Tore · Diff · Pkt`, Zonen-Balken und Legende (Aufstieg/Relegation/Abstieg), eigene Zeile hervorgehoben, Link „Komplette Tabelle“
> - rechts „Nächste Spiele“ mit Heim/Auswärts-Badge, Buttons „Spielplan“ und „Kalender abonnieren“
> - oben ein Team-Umschalter (1. Herren · SG B.K.N. · A-Junioren · Weitere)
>
> Die Mittellinie mit Mittelkreis liegt seitdem zwischen Tabelle und News.

## Design-System V3 (konsolidiert nach Konsistenz-Audit, 21.10.2026)

| Baustein | Regel |
|---|---|
| Section-Kopf | Strich 22×2 `--primary-semi-light` · Kicker 13/16, 600, +0.08em, Versalien, `--text-on-dark-muted` · H2 56/56, −0.02em · rechts: Link **oder** Lead 18/28 **oder** Team-Umschalter · Kopf→Inhalt 40 |
| Section-Abstand | oben 90, unten 60, Gutter 60, Raster 1320, Kachel-Abstand 16 |
| Karte | `--card-bg` (#1A1A1A), 1 px `--card-border`, Radius 10, Innenabstand 28 (große 2×2-Karte 36). Akzentkarte: `--primary` ohne Rahmen (nur eine pro Raster) |
| Karten-Kicker | 12/16, 600, +0.08em, Versalien, `--text-on-dark-muted`, kein Strich |
| Typo | H1 92/84 (Hero) · H2 56/56 · Lead-Karte 48/52 · H3 28/32 (2×1-Karten) · H4 22/26 (1×1-Karten) · Body-L 18/28 · Body 15/23 · Small 13/16 |
| Textstufen auf Dunkel | Weiß (Titel) · `--text-on-dark` 0.8 (Fließtext) · `--text-on-dark-muted` 0.64 (Meta) – keine weiteren Alphas |
| Buttons | Primär (`--primary`), Sekundär (weiß, dunkler Text), Outline (`--outline-on-dark`) · M: 16/20, 16×24 · S: 14/18, 12×18 · Radius 5 |
| Link | 15/18, 600, `--primary-semi-light`, Pfeil inline „Text →“ (auf Rot: weiß) |
| Badge | 11/14, 600, +0.08em, Versalien per CSS, 4×8, Radius 5 · neutral `--badge-bg` (Heim, Auswärts) · Akzent (Kategorien) · Status gefüllt (`--win`/`--draw`/`--loss`) |
| Datum | Fließtext „So 25.10. · 15:00 Uhr“ (Jahr nur außerhalb der Saison) · Datumsblock 56 breit, Radius 5, Zahl Basement 26 + Monat Small |
| Team-Umschalter | eine Komponente für Hero und Tabelle: 1. Herren · SG B.K.N. · Junioren · Mädchen · Ü35 (aus dem Mannschafts-CPT) |
| Tabelle | Kopf im Kicker-Stil; Zonen `--zone-promotion` / `--zone-relegation` / `--zone-relegated`; eigene Zeile `--primary-semi-light` 12 % |
| Begriffe | „Kalender abonnieren“ (Abo) vs. „Zum Kalender hinzufügen“ (einzelnes Spiel) · „Joshua Kimmich Junior Cup“ immer voll · „Partner“ (nicht Sponsoren) · „Probetraining vereinbaren“ |

**Startseiten-Reihenfolge seit V3:** Hero (mit Spieltags-Widget) → Spieltag/Tabelle → Dein FCK (Bento) → Neues vom #FCK → Partner → Footer. Jedes Spiel steht nur noch **einmal**: letztes und nächstes Spiel im Hero, das nächste Heimspiel neben der Tabelle. Die Bento-Kachel „Dein Besuch“ zeigt den Spielort, keinen Gegner.

**Förderverein auf der Startseite (21.10.2026):**
- Partner-Section heißt jetzt „Partner & Förderer“. Unter den Logos stehen zwei gleichwertige Karten: *Partner werden* (für Unternehmen) und *Förderverein* (für Fans, Eltern & Ehemalige) mit „Förderverein beitreten“.
- Die Bento-Kachel „Mithelfen“ verlinkt Förderverein und Ehrenamt.
- Die Termin-Kachel zeigt den Weihnachtsmarkt am 12.12. (der nächste Termin, veranstaltet vom Förderverein).
- Im Footer steht er bei den Ansprechpartnern, im Menü in der neuen Gruppe „Unterstützen“ (Förderverein · Ehrenamt · Partner werden).

**Noch offen (Persona-Review V3):**
- Förderverein: Zweck in einem Satz, Beitrag, Spendenkonto und ggf. Spendenquittung (vom Förderverein liefern lassen)
- ~~Leerzustände gestalten~~ erledigt 21.10.2026, siehe unten
- Partner: echte Logos (SVG) und Fotos vom Verein, Sponsorenmappe als PDF
- Trainer-Service (Platzbelegung, Formulare) als Unterseite verlinken
- Fotos/Galerie, Ferienangebote (Event-Kategorie), sichtbarer Hinweis zu Bild-Einwilligungen
- Registergericht/VR-Nummer für Footer & Impressum
- Mobile Version (9-spaltige Tabelle → `Sp · Diff · Pkt`, Footer-Akkordeon, Chip-Umbruch)

### Seitenrhythmus und Karten-Regel (Persona-Runde V4, 21.10.2026)

Befund: Bento war übernutzt. Ab der Tabelle bestand die Seite aus gleichen dunklen Karten, dadurch wirkte alles gleich wichtig.

**Regel:** Karten nur für klickbare Einheiten mit eigener Aufgabe. Daten, Listen und Logos stehen offen auf dem Grund, getrennt durch Haarlinien (Kopflinie 2 px Weiß 85 %, Zeilen 1 px Weiß 14 %). Bento bleibt nur bei „Dein FCK“ und beim Spieltag-Widget im Hero.

Rhythmus: Hero (Foto) → Tabelle (offen) → Dein FCK (dicht, Bento) → News (Aufmacher + Liste) → HSS-Band (Foto, volle Breite) → Partner-Wand (offen) → Footer (rot).

Weitere Beschlüsse aus der Runde:
- „Probetraining vereinbaren“ nur noch einmal (rote Kachel). Im Jugend-Block steht stattdessen die Jugendleitung mit Foto, Name, Telefon und Mail (Name und Nummer folgen vom Verein).
- „Dein Besuch“ nennt einen Spieltag-Kontakt (Vereinsheim 07725 24 93).
- News-Kopf und Footer verlinken „Presse & Fotos“; Footer nennt „Sponsoring · vorstand@“.
- Kein grauer Fließtext unter 14 px, Labels mindestens 13 px; `--text-on-dark-muted` nur noch sparsam.
- Footer-Navigation in derselben Schrift wie das Menü (Basement Grotesque 800).
- Keine roten CTA-Flächen direkt vor dem roten Footer.

### Partner (21.10.2026, ersetzt das Partner-Bento)

Stufen laut alter Sponsorenseite (abgerufen 21.10.2026): Sportpark/Trikot 1. Herren **HSS Hydraulik und Antriebstechnik GmbH** · Exklusiv: Müller (bm-vorsorge.de), Warrle Personal, Autocenter Heuberg · Premium: Schreinerei Schwarzwälder, EGT, Sprinter VS · Trikot SG: EDEKA Holzky · Präsentationsanzüge: cws workwear · dazu 61 Basis-, Banden-Plus- und Bandenpartner. Pakete und Preise stehen nur in der Sponsorenmappe (auf Anfrage).

Spannung aus der Persona-Runde: Der HSS-CEO will mehr Sichtbarkeit, der Fan fand HSS schon fast zu oft. Lösung: **ein** starker HSS-Moment statt vieler Kacheln.

- **HSS-Band (volle Breite, 620 px):** Foto, Claim „Ohne sie kein Anpfiff.“, Satz zur Rolle (Namensgeber Sportpark, Trikot 1. Herren), Logo in Originalfarben auf weißer Fläche, Links „Jobs & Ausbildung bei HSS ↗“ und „hss-hydraulik.de ↗“ (beide mit UTM, damit HSS Klicks sieht). Foto im Prototyp ist ein Platzhalter; gebraucht wird das HSS-Schild am Sportpark oder das HSS-Trikot. Kein fremdes Trikot im Band, Stadionname immer voll ausgeschrieben, HSS nie im Laufband.
- **Partner-Wand ohne Karten:** Raster mit Haarlinien, Stufen-Label links. Logos einfarbig weiß. Zeilen: Exklusiv (größte Zellen) · Premium · Trikot & Ausstattung (EDEKA Holzky „Trikot SG B.K.N.“, cws „Teamwear“, freier Platz „Hier ist Platz für dich“) · „+ 61 Basis- und Bandenpartner … danke!“ mit Link „Alle Partner mit Namen“ (Unterseite listet alle 61 namentlich).
- **Darunter zwei Textspalten ohne Box:** „Partner werden“ (Ansprechpartner Dietmar Eschner, vorstand@) und „Förderverein“.

**Logo-Proportionen:** Jede Stufe hat eine feste Logo-Fläche. Logos werden nach **Fläche** skaliert, nicht nach Breite: Zielfläche je Stufe, Skalierung `sqrt(Zielfläche / (Breite × Höhe))`, begrenzt auf 80 % Breite und 60 % Höhe der Fläche. Im CPT: Logo als SVG (bevorzugt) mit Feldern „Stufe“, „Rolle“ und „Website“.

Offen vom Verein: Rollenzeilen der Exklusiv-/Premiumpartner, Pakete mit „ab“-Preisen und Sponsorenmappe als PDF, Reichweitenzahlen.

### Mobil 390 px (Paper, Seite „Homepage V3 · Mobil“, 21.10.2026)

Reihenfolge mobil: Hero → Spieltag-Modul → **Dein FCK** → Tabelle → News → HSS-Band → Partner → Footer. „Dein FCK“ steht mobil vor der Tabelle (Wunsch der Eltern-Persona); am Desktop bleibt die Tabelle zuerst.

- **Kopf (sticky):** Wappen, Kurzlink „Spieltag“, roter Menü-Button. Alle Touch-Ziele mindestens 44 px.
- **Spieltag-Modul:** Team-Chips zum Wischen, darunter „Letztes Spiel“ (mit „Spielbericht“ und „Teilen“) und „Nächstes Spiel“ (Anstoß-Box, „Anfahrt“, „In den Kalender“).
- **Dein FCK:** Jugend-Kachel mit Jahrgängen, Linkliste (Trainingszeiten, Kinderschutz, „Für Eltern: Infos teilen“) und Jugendleitung mit Anruf- und Mail-Button · rote Probetraining-Kachel · Termin und Mithelfen als halbe Kacheln · Dein Besuch mit Spieltag-Kontakt · Mitgliedsbeiträge · Teamliste ohne Karte.
- **Tabelle:** fünf Spalten (#, Verein, Sp, Diff, Pkt), Team-Auswahl als Dropdown, offen mit Haarlinien.
- **Partner:** Liste je Stufe (Logo links, Name und Rolle rechts) statt Raster.
- **Footer:** rote Karte, Akkordeon in der Menü-Schrift, Kontakte inkl. Sponsoring und Presse.
- **Menü-Overlay:** rot, große Hauptpunkte als Akkordeon, unten Schnelllinks und „Mitglied werden“.

### Unterseiten Desktop (Paper, Seite „Unterseiten · Desktop“, 21.10.2026)

Alle Unterseiten nutzen dieselbe Kopfzeile (aktiver Punkt weiß), Korn-Grund, Haarlinien statt Karten und den roten Footer. Karten nur für klickbare Einheiten (Spielerkarten, Spielkarten, Formular).

| Seite | Inhalt |
|---|---|
| Aktuelles · Übersicht | Brotkrumen, H1, Filter (Kategorie-Chips + Team-Auswahl), Aufmacher mit Foto, Meldungsliste mit Vorschaubild (Wappen als Platzhalter ohne Foto), Seitenleiste „Nächste Termine“ und „Für Presse & Medien“, „Ältere Meldungen laden“ |
| Aktuelles · Artikel | Meta, H1, Lead, Autor und Lesezeit, Teilen (WhatsApp, Link kopieren), Titelbild mit Bildnachweis, Spalte „Spieldaten“ (Ergebnis, Halbzeit, Ort, Tore aus fussball.de, Tabelle danach), Fließtext 720 px mit Zitat, Ausblick aufs nächste Spiel, „Weitere Meldungen“ |
| Teams · 1. Herren | Hero mit Teamfoto und 70°-Diagonale, Platz, Punkte, Form (S/U/N mit Buchstaben, nicht nur Farbe), Trikotsponsor; Unternavigation; Spielkarten + Tabellenausschnitt + nächste Spiele; Kader nach Positionen (Spielerkarten, weitere Gruppen eingeklappt); Trainerteam, Trainingszeiten, Probetraining; Trikotsponsor-Zeile mit HSS-Jobs-Link |
| Teams · Spielerprofil | Foto-Fläche mit roter 70°-Schräge und Rückennummer, Name, Steckbrief (im Verein seit, starker Fuß, vorherige Vereine, Lieblingsmoment), Saisonzahlen aus fussball.de, vorheriger/nächster Spieler. **Nur für Erwachsene mit Einwilligung, keine Profile für Jugendspieler, kein Geburtsdatum.** |
| Spieltag · Spielplan | Filter nach Team, Umschalter Kommende/Ergebnisse/Nur Heimspiele, Kalender-Abo, Platzbelegung; Liste nach Tagen mit Uhrzeit, Team, Paarung (eigenes Team fett), Ort, Wettbewerb, Anfahrt |
| Partner · Übersicht | HSS-Band, Partner-Wand, alle 61 Partner nach Stufe mit Suche, „Partner werden“ mit vier Paketen (Bande, Trikot, Turniere, Jugend), Sponsorenmappe, Ansprechpartner |
| Verein · Kontakt | Kennzahlen, „Wer hilft dir weiter?“ (Vorstand, Jugend, Kinderschutz, Spielbetrieb, Förderverein, Spieltag), Kontaktformular mit Themenauswahl, Spielstätten mit Route, Formulare & Downloads (Mitgliedsantrag, Satzung, Kinderschutzkonzept, Förderverein) |

Offen vom Verein: echte Spieler- und Trainerfotos, Trainingszeiten je Team, Adressen der Plätze Neuhausen und Buchenberg, Namen der Jugendleitung und der Kinderschutz-Vertrauensperson, Satzung und Kinderschutzkonzept als PDF.

### Keine Hilfs- und Quellentexte auf den Seiten (21.10.2026)

Auf den Seiten stehen keine Quellen- oder Zeitstempelzeilen („Daten: fussball.de“, „Stand …“), keine Erklär- oder Platzhaltertexte („Beispielzeiten …“, „Platzhaltertext …“) und keine Füllsätze. Platzhalter sind realistische Inhalte (Namen, Zeiten), die sich nicht selbst erklären. Herkunft der Daten und offene Inhalte stehen nur in diesem Konzept. Footer-Kontakte zeigen nur die Rolle (Vorstand, Jugend …) als Mail-Link.

### Weitere Seiten (21.10.2026)

- **Mitmachen · Jugend & Eltern (Desktop):** H1 „Dein Kind will kicken?“, Probetraining und „Seite an Eltern schicken“, Jugendleitung in roter 70°-Fläche; Tabelle aller Jahrgänge mit Trainingszeit, Ort und Trainer:in; „Für Eltern“ als aufklappbare Fragen (Kosten, Ausrüstung, Fahrten, Spielgemeinschaft, Absagen); Kinderschutz mit Vertrauensperson und Konzept-PDF.
- **Mobil (390 px):** Artikel (Spieldaten klappbar, Teilen oben), Teamseite (Hero mit Form, Unternavigation zum Wischen, Spielkarten, Tabellenausschnitt, Kader zum Wischen, Training und Trainer), Spielplan (Team-Chips, Kommende/Ergebnisse, Liste nach Tagen), Jugend & Eltern.
- Trainingszeiten und Jahrgänge auf den Entwürfen sind Beispiele; echte Werte kommen aus dem Team-CPT.

### Leerzustände (Paper, Seite „Zustände & Komponenten“, 21.10.2026)

Regeln: nie eine leere Box, sondern Grund und nächsten Schritt nennen · gleiche Karte, gleiche Größe, das Layout springt nicht.

| Modul | Zustand | Auslöser (Daten) | Darstellung |
|---|---|---|---|
| Spieltag-Widget | Spielpause | kein Spiel in den nächsten 21 Tagen | Letztes Spiel bleibt stehen; Karte „Nächstes Spiel“ mit Badge Winter-/Sommerpause, Rückrundenstart nur wenn im Spielplan, Buttons „Kalender abonnieren“ und „Alle Termine“ |
| Spieltag-Widget | Team ohne Liga | Team hat keine fussball.de-ID (Mädchen, Bambini, F) | Eine Karte in der Höhe beider Karten: Badge „Keine Liga“, nächster Termin aus dem CPT oder „Noch nicht eingetragen“, Trainingszeiten, „Probetraining vereinbaren“; Fußzeile „Gepflegt vom Team · Stand …“ |
| Spieltag-Widget | Spieltag heute | Anstoß heute | Badge „Heute“ in `--primary`, Heim/Auswärts wandert in die Datumszeile; kein Live-Ticker |
| Spieltag-Widget | Ergebnis folgt | Anstoß + 2 h vorbei, kein Ergebnis | „– –“ mit neutralem Badge „Ergebnis folgt“, Buttons „fussball.de ↗“ und „Alle Ergebnisse“; nie ein falsches 0:0 |
| Spieltag-Widget | Abgesagt/verlegt | Status von fussball.de | Badge „Abgesagt“ in `--draw`, Uhrzeit durchgestrichen, „Entfällt“, „Nachholtermin folgt“ |
| Tabelle | Team ohne Tabelle | Wettbewerb nur Pokal oder keine Liga | Karte: was das Team spielt, nächste Runde („Auslosung steht noch aus“), Link zum Wettbewerb und zur Teamseite |
| Tabelle | Vor dem 1. Spieltag | alle Spiele = 0 | alphabetisch, ohne Platz und Zonen, eigene Zeile markiert, Hinweis „Die Tabelle startet mit dem 1. Spieltag“ |
| Kachel Termin | Kein Termin | nichts in 60 Tagen | „Gerade ist nichts geplant.“ und „Kalender abonnieren →“ |
| Partner | Platz frei | Stufe hat weniger Partner als Zellen | leere Zelle „Hier ist Platz für dich“ mit „Partner werden ↓“ |
| News | Instagram ohne Einwilligung | kein Consent | kein Embed; Profilname, Hinweis auf die Datenübertragung an Meta, „Beiträge laden“ und „Auf Instagram öffnen ↗“ |

Für die Pipeline heißt das: Import-Zeitstempel, Spielstatus (abgesagt/verlegt) und ein Flag „Team hat Liga/Tabelle“ werden gebraucht, siehe Gaps in Abschnitt 5.

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
- **Förderverein FC Königsfeld e.V.** (eigener Verein, sammelt Geld für den FCK): foerderverein@fc-koenigsfeld.de, Theodor-Heuss-Str. 13, 78126 Königsfeld. Eigener Vorstand, Mitgliedsantrag als PDF. Veranstaltet den **Weihnachtsmarkt** (12.12.2026, Standanfragen an weihnachtsmarkt@fc-koenigsfeld.de). Unterseiten im Altbestand: /foerderverein/vorstand, /kontakt, /weihnachtsmarkt, /impressum. Zweck im Detail, Beitrag und Spendenkonto stehen nirgends, das muss der Förderverein liefern.
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

### Navigation V4 (21.10.2026, ersetzt Schrägsegmente und Menü-Button am Desktop)

Abgeschaut von motion.dev (gesichtet 21.10.2026): Die Leiste ist schwarz-transparent schraffiert wie bei Motion: schwarze Bänder mit schmalen durchsichtigen Lücken, `repeating-linear-gradient(110deg, transparent 0–1,5 px, #121212 1,5–8 px)`, ohne Rahmen. Durch die Lücken scheint der Hintergrund (Hero-Foto oder Seite), die Links bleiben auf dem schwarzen Anteil immer lesbar. Dazu kommen Rolling Text beim Hover und ein schräger Wisch (animierte `clip-path`-Polygonfläche) auf dem CTA. **Ein Winkel für alles:** Die Hero-Diagonale (`linear-gradient(110deg …)`), die Schraffur (110deg) und alle schrägen Kanten (CTA, Menü-Block, 23 × 64 px = 70° zur Waagerechten) laufen parallel.

- **Desktop ohne Menü-Button:** Wappen links, daneben eine durchgehende Leiste mit Schraffur, bündig zum Raster (60 px).
  - Hauptpunkte: Spieltag · Teams · Aktuelles · Verein · Mitmachen · Partner.
  - Rechts der CTA „Mitglied werden“ als roter Block mit schräger linker Kante.
  - Jeder Hauptpunkt ist eine Parallelogramm-Fläche im 70°-Winkel (schräge Kanten 23 × 64 px), im Ruhezustand unsichtbar.
  - **Hover und offenes Panel:** Fläche `--primary`, eingeblendet mit dem schrägen Wisch von links.
  - **Aktive Seite:** Fläche Weiß, Text `#121212`. Auf der Startseite ist kein Punkt aktiv.
- **Mega-Panel:** Jeder Hauptpunkt öffnet bei Hover, Klick oder Fokus ein Panel direkt unter der Leiste, in der Breite der Leiste. Aufbau: Titel mit einem Satz links, zwei Linkspalten, rechts ein Teaser. Der Rest der Seite wird abgedunkelt. `Esc` schließt das Panel, Pfeiltasten führen durch die Links. Damit ersetzen die Panels das Vollbild-Menü am Desktop.
- **Mobil:** Die Kopfzeile bleibt oben stehen und hat dieselbe Schraffur. Links das Wappen, Schnelllink „Spieltag“, rechts der rote Menü-Block mit schräger Kante. Das rote Vollbild-Menü bleibt nur mobil.
- **Hover:** Rolling Text auf allen Links. Auf dem CTA ein schräger Wisch von links (0,4 s, `cubic-bezier(.25,0,0,1)`). Bei `prefers-reduced-motion` gibt es nur einen Farbwechsel.
- Schraffur nur auf Dunkel, nie auf Rot (gleiche Regel wie beim Korn).

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
