# Umzug alte Seite → neue Seite

Wiederholbar. Jedes Objekt trägt `_fck_old_id` und `_fck_old_type`. Ein zweiter Lauf aktualisiert, statt doppelt anzulegen.

1. **Export (alt, nur lesend):** `wp eval-file export.php > export.json`
2. **Vorbereiten (lokal):** `python3 -I prepare.py export.json <ordner>` erzeugt:
   - `import.json`: Export plus die genutzten Medien
   - `files.txt`: die Medien für die Übertragung
3. **Medien übertragen:** `transfer.sh <ssh-alt> <ssh-neu> files.txt`. Kopiert nur genutzte Originale samt Größen.
4. **Import (neu):** `wp eval-file import.php import.json [schritte] --user=<admin>`
   - Mögliche Schritte: `users, media, terms, teams, gegner, personen, partner, posts, seiten, optionen, spiele, fertig`
   - Ohne Angabe laufen alle.
   - Schreibt außerdem `redirects.csv` mit allen alten → neuen URLs.

Die Zuordnung (alte Kategorien, Stufen, Vorstandsrollen, Mannschaften) steht als Tabellen oben in `import.php`.
