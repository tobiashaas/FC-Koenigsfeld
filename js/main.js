/* ───────────────────────────────────────────────────────────────────────────
   FC Königsfeld — Site behaviour
   1. Motion.dev page animations (portiert aus dem Fewo-Rösslewald-Stack):
        • Heading-Reveal — opacity + y + blur, gestaffelt ("Editorial").
        • Image-Reveal   — Curtain (Vorhang) + Ken-Burns-Zoom; Hero Ken-Burns.
      Startzustände stehen in css/motion.css und hängen an `.motion-ready`
      (im <head> nur gesetzt, wenn der Nutzer Motion erlaubt) — Inhalt ist
      ohne JS / unter prefers-reduced-motion immer sichtbar.
   2. Lazy Video.js loader — vendored player + skin werden nur injiziert, wenn
      ein [data-vjs-player] auf der Seite ist. Siehe README für Markup.
   ─────────────────────────────────────────────────────────────────────────── */
(function () {
  "use strict";

  var EASE = [0.22, 1, 0.36, 1];

  /* Reveal-Profile (transform/opacity/filter → Compositor). */
  var REVEAL_Y = { opacity: [0, 1], y: [16, 0] };
  var REVEAL_SCALE = { opacity: [0, 1], scale: [0.98, 1] };
  /* Headline-Reveal: weicher Blur-Fade — "Editorial"-Anmutung. */
  var REVEAL_HEAD = {
    opacity: [0, 1],
    y: [18, 0],
    filter: ["blur(10px)", "blur(0px)"]
  };

  /* ── Motion-Helfer ────────────────────────────────────────────────── */

  /* Promoviert ein Element für die Dauer der Animation auf eine eigene
     Compositor-Layer und gibt sie danach wieder frei (vermeidet "stale
     will-change"). */
  function promote(target, anim, prop) {
    prop = prop || "transform";
    var els = target instanceof Element ? [target] : [].slice.call(target);
    els.forEach(function (el) { el.style.willChange = prop; });
    if (anim && anim.finished && typeof anim.finished.then === "function") {
      anim.finished
        .catch(function () {})
        .finally(function () {
          els.forEach(function (el) { el.style.willChange = ""; });
        });
    }
    return anim;
  }

  function getParts(el) {
    return [].slice.call(el.children);
  }

  /* ── 1a. Heading-Reveal: "Intro"-Stack (Hero / Closing-CTA) ─────────
     Gestaffelter Editorial-Reveal für Heading + Lead + CTA. inView mit
     niedrigem amount feuert für initial sichtbare Hero-Intros sofort und
     für reingescrollte CTAs im richtigen Moment. */
  function initIntro(animate, inView, stagger) {
    document.querySelectorAll("[data-motion-intro]").forEach(function (intro) {
      var parts = getParts(intro);
      inView(intro, function () {
        if (!parts.length) {
          promote(intro, animate(intro, REVEAL_Y, { duration: 0.7, ease: EASE }));
          return;
        }
        promote(
          parts,
          animate(parts, REVEAL_HEAD, {
            duration: 0.95,
            delay: stagger(0.1, { start: 0.05 }),
            ease: EASE
          }),
          "transform, opacity, filter"
        );
      }, { amount: 0.1 });
    });
  }

  /* ── 1b. Heading-Reveal: kompakter Section-Head-Stack ────────────────
     Direkte Kinder (Kicker, Titel, …) staffeln mit Blur-Fade ein. Hat das
     Element keine Kinder, fadet es als Block. */
  function initReveal(animate, inView, stagger) {
    document.querySelectorAll("[data-motion-reveal]").forEach(function (el) {
      var parts = getParts(el);
      if (parts.length) {
        /* Container sichtbar lassen — nur die Parts animieren, sonst läge die
           opacity-0-Layer über den Parts. */
        el.style.opacity = "1";
        el.style.transform = "none";
        el.style.filter = "none";
        inView(el, function () {
          promote(
            parts,
            animate(parts, REVEAL_HEAD, {
              duration: 0.85,
              delay: stagger(0.09),
              ease: EASE
            }),
            "transform, opacity, filter"
          );
        }, { amount: 0.25, margin: "0px 0px -8% 0px" });
        return;
      }
      inView(el, function () {
        promote(el, animate(el, REVEAL_Y, { duration: 0.6, ease: EASE }));
      }, { amount: 0.2, margin: "0px 0px -10% 0px" });
    });
  }

  /* ── 1c. Body-Stagger: Karten, Listen-Items ──────────────────────── */
  function initStagger(animate, inView, stagger) {
    document.querySelectorAll("[data-motion-stagger]").forEach(function (container) {
      var items = [].slice.call(container.children);
      if (!items.length) return;
      inView(container, function () {
        promote(items, animate(items, REVEAL_Y, {
          duration: 0.5,
          delay: stagger(0.06),
          ease: EASE
        }));
      }, { amount: 0.15, margin: "0px 0px -8% 0px" });
    });
  }

  /* ── 1d. Scale-Reveal: einzelne Logos/Marken ─────────────────────── */
  function initRevealScale(animate, inView) {
    document.querySelectorAll("[data-motion-reveal-scale]").forEach(function (el) {
      inView(el, function () {
        promote(el, animate(el, REVEAL_SCALE, { duration: 0.65, ease: EASE }));
      }, { amount: 0.15 });
    });
  }

  /* ── 2a. Curtain-Reveal für Bilder & Medien ──────────────────────────
     Overlay (.motion-curtain) wird von unten nach oben weggewischt; Bild
     zoomt parallel von scale(1.12) → 1 (Ken-Burns). Geschwister-Items im
     selben Wrapper werden automatisch gestaffelt (0.18s). */
  function initCurtain(animate, inView, stagger) {
    var all = [].slice.call(document.querySelectorAll("[data-motion-curtain]"));
    if (!all.length) return;

    var groups = new Map();
    all.forEach(function (item) {
      var group = item.closest("[data-motion-curtain-group]") || item.parentElement;
      if (!groups.has(group)) groups.set(group, []);
      groups.get(group).push(item);
    });

    groups.forEach(function (items) {
      var trigger = items[0];
      var curtains = items
        .map(function (it) { return it.querySelector(".motion-curtain"); })
        .filter(Boolean);
      var media = items
        .map(function (it) { return it.querySelector(".motion-curtain-target, img, video"); })
        .filter(Boolean);
      if (!curtains.length) return;
      var single = items.length === 1;

      inView(trigger, function () {
        promote(
          curtains,
          animate(
            curtains,
            { transform: ["scaleY(1)", "scaleY(0)"] },
            { duration: 1.2, delay: single ? 0 : stagger(0.18), ease: [0.76, 0, 0.24, 1] }
          ),
          "transform"
        );
        if (media.length) {
          promote(
            media,
            animate(
              media,
              { transform: ["scale(1.12)", "scale(1)"] },
              { duration: 1.6, delay: single ? 0 : stagger(0.18), ease: EASE }
            ),
            "transform"
          );
        }
      }, { amount: 0.15, margin: "0px 0px -8% 0px" });
    });
  }

  /* ── 2b. Hero-Bild: Ken-Burns beim Laden ─────────────────────────── */
  function initHeroMedia(animate) {
    document.querySelectorAll("[data-motion-hero-media]").forEach(function (el) {
      promote(el, animate(el, { transform: ["scale(1.12)", "scale(1)"] },
        { duration: 1.6, ease: EASE }), "transform");
    });
  }

  /* ── 1e. Header: dezenter Fade-In ────────────────────────────────── */
  function initHeader(animate) {
    var header = document.querySelector("[data-motion-header]");
    if (!header) return;
    promote(header, animate(header, { opacity: [0, 1], y: [-6, 0] },
      { duration: 0.45, ease: EASE }));
  }

  function initAnimations() {
    var root = document.documentElement;
    var allowed = root.classList.contains("motion-ready");
    var M = window.Motion;

    /* Motion nicht erlaubt oder Library nicht geladen → Startzustände
       neutralisieren, indem `.motion-ready` entfernt wird (CSS macht alles
       sichtbar, Vorhänge bleiben display:none). */
    if (!allowed || !M || typeof M.inView !== "function" || typeof M.animate !== "function") {
      root.classList.remove("motion-ready");
      return;
    }

    var animate = M.animate;
    var inView = M.inView;
    var stagger = M.stagger;

    initHeader(animate);
    initIntro(animate, inView, stagger);
    initReveal(animate, inView, stagger);
    initStagger(animate, inView, stagger);
    initRevealScale(animate, inView);
    initCurtain(animate, inView, stagger);
    initHeroMedia(animate);
  }

  /* ── 3. Lazy Video.js ─────────────────────────────────────────────── */
  function loadCss(href) {
    return new Promise(function (resolve, reject) {
      var link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = href;
      link.onload = resolve;
      link.onerror = reject;
      document.head.appendChild(link);
    });
  }
  function loadScript(src) {
    return new Promise(function (resolve, reject) {
      var s = document.createElement("script");
      s.src = src;
      s.onload = resolve;
      s.onerror = reject;
      document.head.appendChild(s);
    });
  }

  function initVideo() {
    var players = document.querySelectorAll("[data-vjs-player]");
    if (!players.length) return;
    Promise.all([
      loadCss("css/vendor/video-js.min.css"),
      loadScript("js/vendor/video.min.js")
    ])
      .then(function () {
        if (!window.videojs) return;
        players.forEach(function (node) {
          var video = node.tagName === "VIDEO" ? node : node.querySelector("video");
          if (video) window.videojs(video);
        });
      })
      .catch(function (err) {
        console.warn("Video.js konnte nicht geladen werden:", err);
      });
  }

  function init() {
    initAnimations();
    initVideo();
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
