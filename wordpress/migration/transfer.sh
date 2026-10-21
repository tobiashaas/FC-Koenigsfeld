#!/bin/bash
# Überträgt nur die genutzten Medien (Original + Größen-Varianten) von alt nach neu.
# Die Daten laufen durch den lokalen Rechner, es wird nichts zwischengespeichert.
#
# Aufruf: transfer.sh <ssh-alt> <ssh-neu> <files.txt>
#   <ssh-alt>/<ssh-neu>: Befehle, die ein entferntes Kommando ausführen (Wrapper mit Login).
set -euo pipefail
OLD="$1"; NEW="$2"; LIST="$3"

# Liste hochladen, Dateien auf dem alten Server einsammeln und als tar ausgeben.
$OLD 'cat > ~/fck-files.txt' < "$LIST"
$OLD 'cd "$(cd ~/wordpress && wp eval "echo wp_upload_dir()[\"basedir\"];")" && \
  while IFS= read -r stem; do
    for f in "$stem".* "$stem"-[0-9]*x[0-9]*.* "$stem"-scaled.*; do [ -f "$f" ] && printf "%s\n" "$f"; done
  done < ~/fck-files.txt | sort -u > ~/fck-files-full.txt; \
  echo "Dateien: $(wc -l < ~/fck-files-full.txt)" >&2; \
  tar czf - -T ~/fck-files-full.txt; rm -f ~/fck-files.txt ~/fck-files-full.txt' \
| $NEW 'cd ~/files/wp-content/uploads && tar xzf - && echo "angekommen: $(find . -type f | wc -l) Dateien, $(du -sh . | cut -f1)"'
