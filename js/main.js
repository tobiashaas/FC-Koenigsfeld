/* ───────────────────────────────────────────────────────────────────────────
   FC Königsfeld — Site behaviour
   1. Motion.dev scroll-reveal for [data-reveal] elements (quick, unfussy:
      fade + small translateY, ease-out, no bounce / no loops). The initial
      hidden state is set in the <head> via the `.motion-ready` class only when
      the user allows motion — so content is always visible without JS or under
      prefers-reduced-motion.
   2. Lazy Video.js loader — vendored player + skin are injected only when a
      [data-vjs-player] element is on the page, so pages without video pay
      nothing. See README for markup.
   ─────────────────────────────────────────────────────────────────────────── */
(function () {
  "use strict";

  /* ── 1. Scroll reveal ─────────────────────────────────────────────── */
  function initReveal() {
    var root = document.documentElement;
    var allowed = root.classList.contains("motion-ready");
    var targets = document.querySelectorAll("[data-reveal]");
    if (!targets.length) return;

    // Fallback: motion not allowed, or the Motion library failed to load —
    // make sure nothing stays stuck at opacity:0.
    if (!allowed || !window.Motion || typeof window.Motion.inView !== "function") {
      root.classList.remove("motion-ready");
      return;
    }

    var animate = window.Motion.animate;
    var inView = window.Motion.inView;

    targets.forEach(function (elFor) {
      var delay = parseFloat(elFor.getAttribute("data-reveal-delay")) || 0;
      inView(
        elFor,
        function (target) {
          animate(
            target,
            { opacity: [0, 1], transform: ["translateY(18px)", "translateY(0px)"] },
            { duration: 0.5, delay: delay, easing: [0.22, 1, 0.36, 1] }
          );
          // reveal once — no re-trigger on scroll back
          return false;
        },
        { amount: 0.2, margin: "0px 0px -10% 0px" }
      );
    });
  }

  /* ── 2. Lazy Video.js ─────────────────────────────────────────────── */
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
    initReveal();
    initVideo();
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
