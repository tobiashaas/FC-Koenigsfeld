# FC Königsfeld 1954 e.V. — Website

Static front-end prototype for the website of **FC Königsfeld 1954 e.V.**, an
amateur football club in Königsfeld in the Black Forest (Landesliga,
Südbadischer Fußballverband). This repo is the implementation of the
**Homepage V2** design and the groundwork for the eventual rebuild on
**[Etch](https://docs.etchwp.com/) + WordPress**.

The HTML/CSS is written so it ports cleanly to Etch: semantic markup, one
visual block per section, **BEM class names** and **modern native nested CSS**,
all styling class-based (never inline).

## Quick start

```bash
npm install      # dev dependencies (Motion, Video.js)
npm run vendor   # copy their browser builds into js/vendor & css/vendor
npm run serve    # serve at http://localhost:8000  (open index.html)
```

`index.html` is fully static — any static server works. `npm install` /
`npm run vendor` are only needed to refresh the vendored libraries.

## Project structure

```
index.html                Homepage V2
assets/
  fonts/                  self-hosted woff2 + fonts.css (Geist, Geist Mono,
                          Basement Grotesque) — no CDN at runtime
  img/                    crest, hero, sponsor logo, placeholders
css/
  automatic.css           ACSS-aligned framework foundation (tokens + base)
  components.css          reusable UI: .btn, .badge, .tag (BEM + nested)
  homepage.css            page-specific blocks (BEM + nested)
  vendor/                 video-js.min.css
js/
  navigation.js           mega menu + mobile drawer + scroll state
  main.js                 Motion scroll-reveal + lazy Video.js loader
  vendor/                 motion.min.js, video.min.js
scripts/
  vendor.mjs              copies dist files from node_modules → vendor/
```

## CSS: AutomaticCSS (ACSS) alignment

The styling is built on **AutomaticCSS** conventions. `css/automatic.css` is a
**local stand-in** for the ACSS `automatic.css` framework: it declares the exact
ACSS custom-property names (`--primary`, `--neutral-*`, `--space-*`,
`--section-space-*`, `--h1`…`--h6`, `--radius`, `--box-shadow-*`, `--btn-*`,
`--grid-*`, …) with values tuned to the FCK brand (FCK-Rot `--primary #a11212`,
body on `--neutral-ultra-light`, sharp ACSS buttons).

> The ACSS source at `patterns-etch.1wp.site` was **not reachable from the build
> environment**, and (per the brief) didn't yet carry the final values. So the
> foundation here is written from the ACSS-aligned design-system tokens, using
> the same variable names. On the production Etch/WordPress site the real
> `automatic.css` is enqueued by the ACSS plugin — drop `css/automatic.css` and
> the cascade resolves to the plugin's values, no markup changes needed. Retune
> the whole site by editing the `:root` block.

All component/page CSS is **BEM + native nested CSS** (`.btn`,
`.v2-result__score--win`, `&:hover { … }`). The `v2-` block prefix comes from
the design handoff; each block maps to one Etch component.

## Fonts (self-hosted)

All webfonts are downloaded into `assets/fonts/` and declared in
`assets/fonts/fonts.css` — nothing is fetched from Google Fonts or any CDN at
runtime (latin + latin-ext subsets only). Three families: **Geist** (body/UI),
**Geist Mono** (data: scores, times, tables, kicker labels) and **Basement
Grotesque** (display headlines).

## Animation — Motion.dev

Scroll-reveal uses [**Motion**](https://motion.dev) (`window.Motion`, vendored
locally at `js/vendor/motion.min.js`). Sections marked `data-reveal` fade +
rise in once (quick, ease-out `cubic-bezier(0.22, 1, 0.36, 1)`, no bounce, no
loops), per the brand motion guidelines. `prefers-reduced-motion` is fully
respected: the reveal start-state is only applied (via the `.motion-ready`
class set in `<head>`) when motion is allowed, so content is always visible
without JS or under reduced motion.

Add a delay with `data-reveal-delay="0.08"` (seconds).

**Motion+** — the team intends to use [Motion+](https://motion.dev/plus) (the
paid superset: `splitText`, advanced gestures, the cursor/ticker helpers, etc.).
It requires a licensed package that can't be fetched in this environment. When
the license is available, install it and vendor it the same way (extend
`scripts/vendor.mjs`); `js/main.js` is structured so its helpers slot in next to
the current `inView`/`animate` calls.

## Video — Video.js (beta)

[Video.js](https://videojs.com) is vendored locally (the `next` / beta channel,
which works) at `js/vendor/video.min.js` + `css/vendor/video-js.min.css`. It is
**lazy-loaded**: `js/main.js` only injects the player + skin when a
`[data-vjs-player]` element exists on the page, so pages without video (like
this homepage, whose hero is a photo by design) pay nothing.

To add a player anywhere:

```html
<video
  data-vjs-player
  class="video-js"
  controls
  preload="auto"
  poster="assets/img/hero-stadium.jpg">
  <source src="path/to/clip.mp4" type="video/mp4">
</video>
```

## Notes / next steps

- **Imagery** is placeholder (`.v2-media`, crest-watermarked, labelled). Drop
  the club's real freigestellte player cut-outs and news photos in and replace
  each `.v2-media` slot; in WordPress these become dynamic image / ACF fields.
- **Crest** uses the local `assets/img/logo-fck.svg` (the live club URL was not
  reachable from the build environment; everything is local by requirement).
- Sub-pages referenced in the nav/footer (`Matchcenter.html`, `Verein.html`, …)
  are not part of this homepage task; the links are in place for when they land.
