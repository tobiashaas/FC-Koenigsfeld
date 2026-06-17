/* ───────────────────────────────────────────────────────────────────────────
   FC Königsfeld — Navigation: mega menu + mobile drawer + scroll state
   Enhances .v2-header__nav: links whose text matches a MENU key get a
   full-width mega panel on hover/focus; on narrow screens a burger opens a
   drawer with the same structure. No dependencies.
   ─────────────────────────────────────────────────────────────────────────── */
(function () {
  "use strict";

  var MENU = {
    "Mannschaften": {
      tagline: "Bei uns spielt der ganze Ort – von der Landesliga bis zu den Bambini.",
      cols: [
        { title: "Aktive", links: [
          ["1. Mannschaft", "Team.html#erste"],
          ["2. Mannschaft", "Team.html#zweite"],
          ["3. Mannschaft", "Team.html#dritte"],
          ["Alte Herren", "Team.html#ah"] ] },
        { title: "Jugend", links: [
          ["A-Junioren", "Team.html#ja"],
          ["B-Junioren", "Team.html#jb"],
          ["D-Junioren", "Team.html#jd"],
          ["Bambini", "Mannschaften.html#jugend"] ] },
        { title: "Rund ums Team", links: [
          ["Trainerteam", "Mannschaften.html#trainer"],
          ["Der Kader", "Team.html#erste"],
          ["Alle Mannschaften", "Mannschaften.html"] ] }
      ],
      feature: { kicker: "Landesliga", title: "Die Erste 2025/26", text: "Vizemeister – nur die Relegation trennte uns von der Verbandsliga.", href: "Mannschaften.html#erste" }
    },
    "Matchcenter": {
      tagline: "Spielplan, Ergebnisse und die Tabelle – alles auf einen Blick.",
      cols: [
        { title: "Spiele", links: [
          ["Spielplan", "Matchcenter.html#spielplan"],
          ["Ergebnisse", "Matchcenter.html#ergebnisse"],
          ["Live-Ticker", "Matchcenter.html#spielplan"] ] },
        { title: "Wettbewerb", links: [
          ["Tabelle", "Matchcenter.html#tabelle"],
          ["Torjäger", "Matchcenter.html#tabelle"],
          ["Statistiken", "Matchcenter.html#tabelle"] ] }
      ],
      feature: { kicker: "34. Spieltag", title: "Bahlinger SC II – FCK", text: "Sa 21.06. · 15:30 Uhr · Kaiserstuhl-Stadion.", href: "Matchcenter.html#spielplan" }
    },
    "Verein": {
      tagline: "Fußball im Schwarzwald – seit 1954. Lern den FCK kennen.",
      cols: [
        { title: "Über uns", links: [
          ["Der FCK", "Verein.html#ueber"],
          ["Historie", "Historie.html"],
          ["Vorstand & Team", "Vorstand.html"] ] },
        { title: "Anlage", links: [
          ["HSS Hydraulik Sportpark", "Verein.html#sportpark"],
          ["Anfahrt", "Verein.html#sportpark"],
          ["Vereinsheim", "Verein.html#sportpark"] ] },
        { title: "Service", links: [
          ["Kontakt", "Verein.html#kontakt"],
          ["Downloads", "Verein.html#kontakt"],
          ["Satzung", "Verein.html#kontakt"] ] }
      ],
      feature: { kicker: "Seit 1954", title: "70 Jahre FCK", text: "Die Geschichte eines Dorfes und seiner Leidenschaft für den Fußball.", href: "Historie.html" }
    },
    "Mitmachen": {
      tagline: "Ob auf dem Platz oder am Spielfeldrand – beim FCK ist Platz für alle.",
      cols: [
        { title: "Aktiv werden", links: [
          ["Mitglied werden", "Mitmachen.html#mitglied"],
          ["Probetraining", "Mitmachen.html#mitglied"],
          ["Ehrenamt", "Mitmachen.html#ehrenamt"] ] },
        { title: "Unterstützen", links: [
          ["Förderverein", "Mitmachen.html#foerderverein"],
          ["Sponsor werden", "Sponsoren.html"],
          ["Spenden", "Mitmachen.html#foerderverein"] ] }
      ],
      feature: { kicker: "Rot-Weiß", title: "Werde Teil des #FCK", text: "Mitglied, Ehrenamtliche:r oder Sponsor – jede Hand zählt.", href: "Mitmachen.html#mitglied" }
    },
    "Medien": {
      tagline: "Spielberichte, Vereinsnews und alles aus dem Nachwuchs.",
      cols: [
        { title: "Aktuelles", links: [
          ["Alle News", "Medien.html"],
          ["Spielberichte", "Medien.html"],
          ["Vereinsnews", "Medien.html"],
          ["Jugend", "Medien.html"] ] },
        { title: "Mehr", links: [
          ["Veranstaltungen", "Veranstaltungen.html"],
          ["Matchcenter", "Matchcenter.html"],
          ["#FCK auf Instagram", "#"] ] }
      ],
      feature: { kicker: "Spielbericht", title: "3:1 zum Saisonende", text: "Verdienter Heimsieg im letzten Spiel der Saison.", href: "Artikel.html" }
    },
    "Förderverein": {
      tagline: "Freunde & Förderer des FCK – für Jugend, Anlage und Vereinsleben.",
      cols: [
        { title: "Förderverein", links: [
          ["Vorstand", "Foerderverein.html#vorstand"],
          ["Kontakt", "Foerderverein.html#kontakt"],
          ["Weihnachtsmarkt", "Foerderverein.html#weihnachtsmarkt"],
          ["Impressum", "Foerderverein.html#impressum"] ] },
        { title: "Unterstützen", links: [
          ["Förderer werden", "Foerderverein.html#mitglied"],
          ["Sponsor werden", "Sponsoren.html"],
          ["Spenden", "Foerderverein.html#kontakt"] ] }
      ],
      feature: { kicker: "Seit 1998", title: "Ab 40 € im Jahr dabei", text: "Dein Beitrag bleibt komplett beim FCK – für den Nachwuchs.", href: "Foerderverein.html#mitglied" }
    }
  };

  function el(tag, cls, html) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (html != null) n.innerHTML = html;
    return n;
  }

  function buildPanelContent(data) {
    var inner = el("div", "v2-mega__inner");
    var cols = el("div", "v2-mega__cols");
    data.cols.forEach(function (c) {
      var col = el("div", "v2-mega__col");
      col.appendChild(el("div", "v2-mega__col-title", c.title));
      var ul = el("ul", "v2-mega__list");
      c.links.forEach(function (l) {
        var li = el("li");
        var a = el("a", "v2-mega__link", l[0]);
        a.href = l[1];
        li.appendChild(a);
        ul.appendChild(li);
      });
      col.appendChild(ul);
      cols.appendChild(col);
    });
    inner.appendChild(cols);
    if (data.feature) {
      var f = data.feature;
      var feat = el("a", "v2-mega__feature");
      feat.href = f.href;
      feat.innerHTML =
        '<span class="v2-mega__feature-kicker"></span>' +
        '<span class="v2-mega__feature-title"></span>' +
        '<span class="v2-mega__feature-text"></span>' +
        '<span class="v2-mega__feature-go">Mehr erfahren →</span>';
      feat.querySelector(".v2-mega__feature-kicker").textContent = f.kicker;
      feat.querySelector(".v2-mega__feature-title").textContent = f.title;
      feat.querySelector(".v2-mega__feature-text").textContent = f.text;
      inner.appendChild(feat);
    }
    return inner;
  }

  function initDesktop(header, nav) {
    var panel = el("div", "v2-mega");
    panel.setAttribute("role", "region");
    header.appendChild(panel);
    var closeTimer = null;
    var current = null;

    function open(key, link) {
      if (!MENU[key]) return;
      if (current !== key) {
        panel.innerHTML = "";
        panel.appendChild(buildPanelContent(MENU[key]));
        current = key;
      }
      [].forEach.call(nav.querySelectorAll(".v2-header__link"), function (l) {
        l.classList.toggle("is-hovered", l === link);
      });
      panel.classList.add("is-open");
      clearTimeout(closeTimer);
    }
    function scheduleClose() {
      clearTimeout(closeTimer);
      closeTimer = setTimeout(close, 140);
    }
    function close() {
      panel.classList.remove("is-open");
      current = null;
      [].forEach.call(nav.querySelectorAll(".v2-header__link"), function (l) {
        l.classList.remove("is-hovered");
      });
    }

    [].forEach.call(nav.querySelectorAll(".v2-header__link"), function (link) {
      var key = (link.textContent || "").trim();
      if (!MENU[key]) {
        link.addEventListener("mouseenter", scheduleClose);
        return;
      }
      link.setAttribute("aria-haspopup", "true");
      link.addEventListener("mouseenter", function () { open(key, link); });
      link.addEventListener("focus", function () { open(key, link); });
    });

    panel.addEventListener("mouseenter", function () { clearTimeout(closeTimer); });
    panel.addEventListener("mouseleave", scheduleClose);
    nav.addEventListener("mouseleave", scheduleClose);
    header.addEventListener("focusout", function (e) {
      if (!header.contains(e.relatedTarget)) close();
    });
    document.addEventListener("keydown", function (e) { if (e.key === "Escape") close(); });
  }

  function initMobile(header, nav) {
    var inner = header.querySelector(".v2-header__inner");
    var burger = el("button", "v2-burger", "<span></span><span></span><span></span>");
    burger.setAttribute("aria-label", "Menü öffnen");
    burger.setAttribute("aria-expanded", "false");
    inner.appendChild(burger);

    var drawer = el("div", "v2-drawer");
    var dInner = el("div", "v2-drawer__inner");
    drawer.appendChild(dInner);

    [].forEach.call(nav.querySelectorAll(".v2-header__link"), function (link) {
      var key = (link.textContent || "").trim();
      var group = el("div", "v2-drawer__group");
      var head = el("a", "v2-drawer__head", key);
      head.href = link.getAttribute("href") || "#";
      group.appendChild(head);
      if (MENU[key]) {
        var ul = el("ul", "v2-drawer__list");
        MENU[key].cols.forEach(function (c) {
          c.links.forEach(function (l) {
            var li = el("li");
            var a = el("a", "v2-drawer__link", l[0]);
            a.href = l[1];
            li.appendChild(a);
            ul.appendChild(li);
          });
        });
        group.appendChild(ul);
      }
      dInner.appendChild(group);
    });
    document.body.appendChild(drawer);

    function toggle(openIt) {
      var willOpen = openIt != null ? openIt : !drawer.classList.contains("is-open");
      drawer.classList.toggle("is-open", willOpen);
      burger.classList.toggle("is-active", willOpen);
      burger.setAttribute("aria-expanded", String(willOpen));
      burger.setAttribute("aria-label", willOpen ? "Menü schließen" : "Menü öffnen");
      document.documentElement.style.overflow = willOpen ? "hidden" : "";
    }
    burger.addEventListener("click", function () { toggle(); });
    drawer.addEventListener("click", function (e) {
      if (e.target === drawer || e.target.classList.contains("v2-drawer__link") || e.target.classList.contains("v2-drawer__head")) toggle(false);
    });
    document.addEventListener("keydown", function (e) { if (e.key === "Escape") toggle(false); });
  }

  function init() {
    var header = document.querySelector(".v2-header");
    var nav = header && header.querySelector(".v2-header__nav");
    if (!header || !nav) return;
    initDesktop(header, nav);
    initMobile(header, nav);
    // floating header gains a stronger shadow once scrolled
    var onScroll = function () { header.classList.toggle("is-scrolled", window.scrollY > 8); };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
