#!/usr/bin/env python3
"""
Bereitet den Import vor (lokal, ohne Netz).

Liest export.json, ermittelt die Medien, die übernommene Inhalte wirklich nutzen,
und schreibt:
  import.json   Export + Liste der genutzten Medien-IDs
  files.txt     Datei-Stämme für die Übertragung (Original + Größen-Varianten)

Aufruf: python3 -I prepare.py <export.json> <ausgabe-ordner>
"""
import json
import os
import re
import sys

src, out = sys.argv[1], sys.argv[2]
d = json.load(open(src))

media = {m["old_id"]: m for m in d["media"]}
by_file = {}
for m in d["media"]:
    stem, _ = os.path.splitext(m["file"])
    by_file[stem] = m["old_id"]

own_club = {v["old_id"] for v in d["vereine"] if "königsfeld" in v["title"].lower()}
used = set()


def add(i):
    if i and int(i) in media:
        used.add(int(i))


def from_content(html):
    for i in re.findall(r"wp-image-(\d+)", html):
        add(i)
    for block in re.findall(r"<!-- wp:(?:image|file|video|cover|gallery|media-text) (\{.*?\}) /?-->", html):
        try:
            attrs = json.loads(block)
        except ValueError:
            continue
        for key in ("id", "mediaId"):
            add(attrs.get(key))
        for i in attrs.get("ids", []) or []:
            add(i)
    for path in re.findall(r"/wp-content/uploads/([^\"'\s)>?]+)", html):
        stem, _ = os.path.splitext(path)
        stem = re.sub(r"-(\d+x\d+|scaled|e\d{10,})$", "", stem)
        if stem in by_file:
            used.add(by_file[stem])


for p in d["posts"] + d["historie"] + d["foerderverein"] + d["rechtlich"] + d["pages"]:
    add(p["thumb"])
    from_content(p["content"])
for t in d["mannschaften"]:
    add(t["thumb"])
for v in d["vereine"]:
    if v["old_id"] not in own_club:
        add(v["thumb"])
def norm(s):
    s = s.lower()
    for a, b in (("ä", "ae"), ("ö", "oe"), ("ü", "ue"), ("ß", "ss"), ("&amp;", "&")):
        s = s.replace(a, b)
    return re.sub(r"[^a-z0-9]", "", s)


# Viele Sponsor-Logos sind nicht verknüpft, tragen aber den Firmennamen als Titel oder Dateinamen.
logo_by_name = {}
for m in d["media"]:
    if m["mime"] in ("image/svg+xml", "image/png", "image/webp", "image/jpeg"):
        for key in (norm(m["title"]), norm(os.path.splitext(os.path.basename(m["file"]))[0])):
            if key:
                # SVG vor Pixelbild
                if key not in logo_by_name or m["mime"] == "image/svg+xml":
                    logo_by_name[key] = m["old_id"]
guessed = 0
for s in d["sponsoren"]:
    if not s["logo"]:
        for key in (norm(s["title"]), norm(s["slug"])):
            if key in logo_by_name:
                s["logo"] = logo_by_name[key]
                s["logo_geraten"] = True
                guessed += 1
                break
    add(s["logo"])
print(f"Sponsor-Logos über Namen zugeordnet: {guessed}, ohne Logo: {sum(1 for s in d['sponsoren'] if not s['logo'])}")
for m in d["mitglieder"]:
    add(m["player_photo"] or m["board_member_photo"])
add((d["einstellungen"].get("logo_group") or {}).get("logo"))

# Schriften und Videos aus Bricks-Zeiten nicht übernehmen.
used = {i for i in used if not media[i]["mime"].startswith(("font/", "video/"))}

d["used_media"] = sorted(used)
d["own_club_ids"] = sorted(own_club)
os.makedirs(out, exist_ok=True)
json.dump(d, open(os.path.join(out, "import.json"), "w"), ensure_ascii=False)

with open(os.path.join(out, "files.txt"), "w") as f:
    for i in sorted(used):
        f.write(os.path.splitext(media[i]["file"])[0] + "\n")

mimes = {}
for i in used:
    mimes[media[i]["mime"]] = mimes.get(media[i]["mime"], 0) + 1
print(f"Medien genutzt: {len(used)} von {len(media)}", mimes)
