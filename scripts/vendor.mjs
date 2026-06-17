/**
 * Copies the browser-ready dist files of our runtime dependencies
 * (Motion, Video.js) out of node_modules into the committed, self-hosted
 * vendor folders. Everything the site loads at runtime lives in the repo —
 * no CDN, no node_modules dependency in production. Re-run after `npm install`
 * or a dependency bump:  npm run vendor
 */
import { copyFileSync, mkdirSync, readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const nm = join(root, 'node_modules');
const version = (pkg) =>
  JSON.parse(readFileSync(join(nm, pkg, 'package.json'), 'utf8')).version;

const jobs = [
  // Motion — UMD build, exposes window.Motion
  ['motion/dist/motion.js', 'js/vendor/motion.min.js', 'motion'],
  // Video.js (beta / next channel) — player + skin
  ['video.js/dist/video.min.js', 'js/vendor/video.min.js', 'video.js'],
  ['video.js/dist/video-js.min.css', 'css/vendor/video-js.min.css', 'video.js'],
];

mkdirSync(join(root, 'js/vendor'), { recursive: true });
mkdirSync(join(root, 'css/vendor'), { recursive: true });

for (const [from, to, pkg] of jobs) {
  copyFileSync(join(nm, from), join(root, to));
  console.log(`vendored ${pkg}@${version(pkg)}  →  ${to}`);
}
