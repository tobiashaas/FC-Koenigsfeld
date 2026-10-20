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
      feature: { kicker: "Seit 1954", title: "Unsere Geschichte", text: "Die Geschichte eines Dorfes und seiner Leidenschaft für den Fußball.", href: "Historie.html" }
    },
    "Mitmachen": {
      tagline: "Ob auf dem Platz oder am Spielfeldrand – beim FCK ist Platz für alle.",
      cols: [
        { title: "Aktiv werden", links: [
          ["Mitglied werden", "Mitmachen.html#mitglied"],
          ["Probetraining", "Mitmachen.html#probetraining"],
          ["Ehrenamt", "Mitmachen.html#ehrenamt"] ] },
        { title: "Unterstützen", links: [
          ["Förderverein", "Mitmachen.html#foerderverein"],
          ["Sponsor werden", "Sponsoren.html#partner-werden"],
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
          ["#FCK auf Instagram", "https://www.instagram.com/fckoenigsfeld1954/"] ] }
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
          ["Sponsor werden", "Sponsoren.html#partner-werden"],
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
    panel.id = "v2-mega-panel";
    panel.setAttribute("role", "region");
    header.appendChild(panel);
    var closeTimer = null;
    var current = null;
    var currentLink = null;
    var links = [].slice.call(nav.querySelectorAll(".v2-header__link"));

    function open(key, link) {
      if (!MENU[key]) return;
      if (current !== key) {
        panel.innerHTML = "";
        panel.appendChild(buildPanelContent(MENU[key]));
        panel.setAttribute("aria-label", key);
        current = key;
      }
      links.forEach(function (l) {
        var on = l === link;
        l.classList.toggle("is-hovered", on);
        if (l.hasAttribute("aria-expanded")) l.setAttribute("aria-expanded", String(on));
      });
      currentLink = link;
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
      links.forEach(function (l) {
        l.classList.remove("is-hovered");
        if (l.hasAttribute("aria-expanded")) l.setAttribute("aria-expanded", "false");
      });
    }
    function panelLinks() {
      return [].slice.call(panel.querySelectorAll("a"));
    }

    links.forEach(function (link, i) {
      var key = (link.textContent || "").trim();
      if (!MENU[key]) {
        link.addEventListener("mouseenter", scheduleClose);
        link.addEventListener("focus", close);
        return;
      }
      link.setAttribute("aria-expanded", "false");
      link.setAttribute("aria-controls", panel.id);
      link.addEventListener("mouseenter", function () { open(key, link); });
      link.addEventListener("focus", function () { open(key, link); });
      // Keyboard: ArrowDown or Tab moves into the open panel, so its links are reachable
      link.addEventListener("keydown", function (e) {
        var first = panelLinks()[0];
        if (!first) return;
        if (e.key === "ArrowDown" || (e.key === "Tab" && !e.shiftKey)) {
          e.preventDefault();
          open(key, link);
          first.focus();
        }
      });
    });

    panel.addEventListener("keydown", function (e) {
      var items = panelLinks();
      var idx = items.indexOf(document.activeElement);
      if (e.key === "Escape" && currentLink) {
        e.preventDefault();
        var back = currentLink;
        close();
        back.focus();
      } else if (e.key === "Tab" && !e.shiftKey && idx === items.length - 1) {
        // leaving the panel forwards → continue with the next top-level item
        var next = links[links.indexOf(currentLink) + 1] || nav.querySelector(".btn");
        if (next) { e.preventDefault(); close(); next.focus(); }
      } else if (e.key === "Tab" && e.shiftKey && idx === 0 && currentLink) {
        e.preventDefault();
        currentLink.focus();
      }
    });

    panel.addEventListener("mouseenter", function () { clearTimeout(closeTimer); });
    panel.addEventListener("mouseleave", scheduleClose);
    nav.addEventListener("mouseleave", scheduleClose);
    header.addEventListener("focusout", function (e) {
      if (!header.contains(e.relatedTarget)) close();
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && panel.classList.contains("is-open")) {
        var back = currentLink;
        close();
        if (back && header.contains(document.activeElement)) back.focus();
      }
    });
  }

  function initMobile(header, nav) {
    var inner = header.querySelector(".v2-header__inner");
    var page = document.querySelector(".v2");
    var burger = el("button", "v2-burger", "<span></span><span></span><span></span>");
    burger.type = "button";
    burger.setAttribute("aria-label", "Menü öffnen");
    burger.setAttribute("aria-expanded", "false");
    burger.setAttribute("aria-controls", "v2-drawer");
    inner.appendChild(burger);

    var drawer = el("div", "v2-drawer");
    drawer.id = "v2-drawer";
    var dInner = el("div", "v2-drawer__inner");
    dInner.setAttribute("role", "dialog");
    dInner.setAttribute("aria-modal", "true");
    dInner.setAttribute("aria-label", "Hauptmenü");
    drawer.appendChild(dInner);

    // Top bar with an always-visible close button
    var bar = el("div", "v2-drawer__bar");
    bar.appendChild(el("span", "v2-drawer__title", "Menü"));
    var closeBtn = el("button", "v2-drawer__close",
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>');
    closeBtn.type = "button";
    closeBtn.setAttribute("aria-label", "Menü schließen");
    bar.appendChild(closeBtn);
    dInner.appendChild(bar);

    // One accordion per top-level item; items without a sub menu are plain links
    var dNav = el("nav", "v2-drawer__nav");
    dNav.setAttribute("aria-label", "Hauptnavigation");
    links(nav).forEach(function (link) {
      var key = (link.textContent || "").trim();
      var href = link.getAttribute("href") || "#";
      if (!MENU[key]) {
        var plain = el("div", "v2-drawer__group");
        var a = el("a", "v2-drawer__head");
        a.textContent = key;
        a.href = href;
        plain.appendChild(a);
        dNav.appendChild(plain);
        return;
      }
      var group = el("details", "v2-drawer__group");
      var head = el("summary", "v2-drawer__head");
      head.textContent = key;
      group.appendChild(head);
      var ul = el("ul", "v2-drawer__list");
      var overview = el("a", "v2-drawer__link v2-drawer__link--overview");
      overview.textContent = key + " – Übersicht";
      overview.href = href;
      var li0 = el("li");
      li0.appendChild(overview);
      ul.appendChild(li0);
      MENU[key].cols.forEach(function (c) {
        c.links.forEach(function (l) {
          var li = el("li");
          var a = el("a", "v2-drawer__link");
          a.textContent = l[0];
          a.href = l[1];
          li.appendChild(a);
          ul.appendChild(li);
        });
      });
      group.appendChild(ul);
      dNav.appendChild(group);
    });
    dInner.appendChild(dNav);

    // Primary CTA pinned to the bottom (thumb zone), taken from the header button
    var headerCta = nav.querySelector(".btn");
    if (headerCta) {
      var foot = el("div", "v2-drawer__foot");
      var cta = el("a", "btn btn--lg");
      cta.textContent = headerCta.textContent.trim();
      cta.href = headerCta.getAttribute("href") || "#";
      foot.appendChild(cta);
      dInner.appendChild(foot);
    }

    document.body.appendChild(drawer);

    function focusables() {
      return [].slice.call(dInner.querySelectorAll("a[href], button, summary"));
    }

    function toggle(openIt) {
      var willOpen = openIt != null ? openIt : !drawer.classList.contains("is-open");
      if (willOpen === drawer.classList.contains("is-open")) return;
      drawer.classList.toggle("is-open", willOpen);
      burger.classList.toggle("is-active", willOpen);
      burger.setAttribute("aria-expanded", String(willOpen));
      burger.setAttribute("aria-label", willOpen ? "Menü schließen" : "Menü öffnen");
      document.documentElement.style.overflow = willOpen ? "hidden" : "";
      // Page behind the dialog is inert while the drawer is open
      if (page) page.inert = willOpen;
      if (willOpen) closeBtn.focus();
      else burger.focus();
    }

    burger.addEventListener("click", function () { toggle(); });
    closeBtn.addEventListener("click", function () { toggle(false); });
    drawer.addEventListener("click", function (e) {
      if (e.target === drawer || (e.target.closest && e.target.closest("a.v2-drawer__link, a.v2-drawer__head, .v2-drawer__foot a"))) toggle(false);
    });
    document.addEventListener("keydown", function (e) {
      if (!drawer.classList.contains("is-open")) return;
      if (e.key === "Escape") { toggle(false); return; }
      if (e.key !== "Tab") return;
      // Focus trap
      var items = focusables().filter(function (n) { return n.offsetParent !== null; });
      var first = items[0], last = items[items.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });
    // Leaving the mobile breakpoint closes the drawer
    window.matchMedia("(min-width: 981px)").addEventListener("change", function (mq) {
      if (mq.matches) toggle(false);
    });
  }

  function links(nav) {
    return [].slice.call(nav.querySelectorAll(".v2-header__link"));
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
