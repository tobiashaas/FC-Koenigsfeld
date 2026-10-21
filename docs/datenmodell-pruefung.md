# Datenmodell: Prüfung mit Personas (22.10.2026)

Geprüft wurde `fck-core` 0.2 (Felder) und die Etch-Inhaltstypen gegen den Alltag von:
- Trainer:in E-Jugend, Jugendleitung und Eltern
- Vorstand, Redaktion und Förderverein
- HSS-CEO, Bandensponsor und Interessent, Fan und Gastverein
- Entwickler:in und Datenschutz

Ergebnis: Spieldaten und Teams tragen. Bei Zuständigkeiten, Datenschutz, Saison, Gegnern und Partnern ist das Modell noch nicht dicht.

## Von mehreren Personas genannt (Muss)

| Thema | Problem | Änderung |
|---|---|---|
| **Ämter und Zuständigkeiten** | Eine Rolle gilt pro Person, nicht pro Team. „Spieler 1. Herren, Trainer E2, Kassier“ lässt sich nicht abbilden. Es gibt keine Amtszeit und keine Reihenfolge, und nach einer Wahl geht der alte Stand verloren. | Neuer interner Typ **`amt`**: Person, Organisation (FCK/Förderverein), Gremium (Vorstand, Jugendleitung, Trainer, Betreuer, Kinderschutz, Ehrenmitglied), Funktion, Mannschaft (optional), von/bis, Sortierung, Funktions-E-Mail. An der Person bleibt `mannschaften` nur für „spielt in“. |
| **Rechte für Trainer:innen** | Eine Person ist kein Login. Es gibt nichts, woraus sich „nur eigene Mannschaft“ ableiten ließe. | `user_id` an der Person. Rechte nur aus `amt` (Trainer/Betreuer + Mannschaft). Eigene Rechte für `spiel`, `tabelle` und `person` (`capability_type`), dazu die Rolle `fck_sync` für die Pipeline. |
| **Datenschutz Personen** | Meta Box gibt Felder über REST öffentlich aus, auch Telefon und E-Mail. „Profil ausblenden“ setzt nichts durch. Bei Minderjährigen gibt es keine Regel. | Kontaktfelder `hide_from_rest`. Mit „Profil ausblenden“ wird die Person *privat* gesetzt und verschwindet damit überall. Jugendspieler:innen bekommen kein öffentliches Profil (Altersklasse mit Merkmal `jugend`). `kontakt_oeffentlich` ist standardmäßig aus, sonst Funktionsadresse. |
| **Saison** | Spiele und Tabellen haben keine Saison. Die Tabelle wird überschrieben, „letzte 5“ mischt im Sommer zwei Saisons. Die fussball.de-ID veraltet unbemerkt (D-Junioren). | Taxonomie **`saison`** an Spiel und Tabelle, eine Tabelle pro Mannschaft und Saison. Sync-Status an der Mannschaft: letzter Lauf, Fehler, fussball.de-Saison. |
| **Gegner** | Das Wappen hängt an jedem Spiel und jeder Tabellenzeile. Die 128 alten „Vereine“ haben kein Ziel. | Interner Typ **`gegner`**: Kurzname, fussball.de-Namen (mehrere), Wappen, Platzadresse, Koordinaten. Am Spiel und in der Tabellenzeile `gegner_id`. |
| **Wettbewerb und Turniere** | `wettbewerb` ist Freitext. Turniere und Spielfeste passen nicht, weil ein Gegner Pflicht ist. | Taxonomie **`wettbewerb`** (Art als Merkmal: Liga/Pokal/Test/Turnier). Am Spiel `ende` und `turnier_name`, der Gegner ist nur bei Liga, Pokal und Test Pflicht. |
| **Spielstatus** | Abbruch, Nichtantritt und gewertete Ergebnisse fehlen. Bei einer Verlegung geht der alte Termin verloren. | Status `abgebrochen` und `nichtantritt`. Neue Felder `wertung_eigen`/`wertung_gegner`, `anstoss_urspruenglich`, `nachholspiel`, Halbzeitstand und `manuell_gesperrt` (der Sync überschreibt dann keine Korrekturen). |
| **Partner** | Stufe und Leistung sind vermischt: HSS hat Sportpark, Trikot und Bande. Der Trikotsponsor ist doppelt gespeichert. Nach Ende der Laufzeit blendet nichts aus. Für das HSS-Band fehlen die Felder. | Gruppe `leistungen` (Art, Mannschaft, Text). Trikot nur noch dort. Laufzeit von/bis mit „unbefristet“, ein täglicher Cron setzt abgelaufene Partner auf einen eigenen Status. Neue Felder `claim`, `band_foto`, `links`, `logo_hell`/`logo_dunkel` und `stufe_rang` für die Sortierung. Auf der Options-Seite `hauptpartner_id`. |
| **Technik Pipeline** | Zeitzone ungeklärt. `fd_spiel_id` ist nicht eindeutig, überlappende Läufe erzeugen Dubletten. Etch kennt kein „jetzt“. | `anstoss` ist Berliner Ortszeit, und nur `fck-core` rechnet um. Spiel-Slug `fd-<id>`, dazu eine Sperre pro Lauf. Rechenwerte als Felder: an der Mannschaft `naechstes_spiel_id`, `letztes_spiel_id`, `form`; am Spiel `ausgang` und `tag`. Neu berechnet nach Sync, Speichern und Anstoß, danach Cache leeren. |
| **Training** | Es gibt keinen Ort für eine kurzfristige Absage. Hallen- und Feldsaison muss man jedes Mal neu eintippen. | Gruppe `ausfaelle` (Datum, Grund) an der Mannschaft. In den Trainingszeiten `zeitraum` (ganzjährig/Feld/Halle), auf der Options-Seite Beginn und Ende der Hallensaison. |
| **Förderverein** | Er ist nur über `veranstalter` angedeutet. Es fehlen Kontakt, Spendenkonto und Antrag. | Taxonomie **`organisation`** (FCK/Förderverein) für Beiträge, Termine, Downloads, Ämter und Projekte. Eigene Options-Seite „Förderverein“ mit eigenem Recht. |

## Sollte

- **Spielgemeinschaft:** `sg`, `sg_name`, `sg_partner`, `fd_team_name` (für den Abgleich der Tabelle). Dazu `geschlecht` an der Mannschaft. Bambini werden Altersklasse G, Schiedsrichter sind keine Mannschaft, sondern ein Amt.
- **Jahrgänge rechnen:** U-von/bis an der Altersklasse, `saison_start` als Datum. Jahrgänge nur noch als Ausnahme von Hand.
- **Projekte** (Kunstrasen u. a.): Typ `projekt` mit Status, Ziel- und Stand-Betrag, Zeitraum, Galerie.
- **Beiträge** (Geld) als Gruppe: Organisation, Bezeichnung, Betrag, Intervall, gültig ab.
- **Downloads:** Stand, Organisation, „ersetzt“ (Satzung in Versionen), Bereiche Jugend, Kinderschutz und Trainer.
- **Termine:** Art, Reihe (Kimmich-Cup jedes Jahr), Anmeldung, Status abgesagt, Nachbericht.
- **Beiträge (News):** Galerie, `autor_name`, „angepinnt bis“. Der Spielbericht setzt die Mannschaft automatisch aus dem Spiel.
- **Spielstätte:** offizieller Name, Namensgeber → Partner (HSS-Sportpark), einzelne Plätze mit Belag, Kabinen, ÖPNV, Parkplatz-Koordinaten.
- **Partner werden:** Pakete, Ansprechperson, Sponsorenmappe, Reichweite (Options-Tab „Partner“).
- **Probetraining:** pro Mannschaft möglich ja/nein und Kontakt. Formulareinträge werden nach 90 Tagen gelöscht.
- **Team-Sync:** Slug-Konflikte melden, verwaiste Begriffe beim Löschen aufräumen, 301 bei Slug-Wechsel.
- **Sitemaps:** bewusst wählen, was hineinkommt (keine Jugend-Einzelseiten).

## Kann

- Rückennummer pro Mannschaft
- Torschützen-Liste nur bei Erwachsenen
- Elternhinweise pro Team
- Ehrenamt-Aufgaben
- Öffnungszeiten Vereinsheim
- Erinnerung vor Ende einer Partner-Laufzeit

## Folgen für den Umzug

- `vereine` (128) → `gegner`, Wappen einmal in die Mediathek.
- `mitglieder` → `person`. Vorstand → `amt` mit Funktion aus `board_role`. Spieler-Zuordnung aus `mannschaft-spieler`.
- Keine Vorstandsadressen und keine Jugendfotos ohne Klärung übernehmen. Medien nur, wenn ein übernommener Inhalt sie nutzt; beim Verkleinern EXIF/GPS entfernen.
- Themen-Kategorien (Kunstrasen, Neubau, Pokale, Weihnachten) → Schlagwörter oder Projekt. Die Art-Kategorien → die neuen Kategorien.
- Historie → Typ `historie` (nicht Seiten).
