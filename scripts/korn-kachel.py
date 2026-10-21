"""Seamless grain tile for the dark page ground (#121212).

Same noise as prototypes/seiten-hintergrund.html (theme=dark, contours=0, chalk=0),
but without the top light glow, so it tiles without seams on every section,
the footer and the menu. Usage: python3 scripts/korn-kachel.py
"""
import numpy as np
from PIL import Image

SIZE = 600            # tile edge in px; white noise tiles invisibly
GROUND = 0.070        # #121212
GRAIN = 0.080 * 0.90  # shader grain strength with contours off

rng = np.random.default_rng(1954)
noise = (rng.random((SIZE, SIZE)) + rng.random((SIZE, SIZE))) * 0.5 - 0.5
value = np.clip(GROUND + noise * GRAIN, 0, 1)
img = (np.round(value * 255)).astype(np.uint8)
Image.fromarray(np.stack([img] * 3, axis=-1)).save("prototypes/bg-korn-kachel.png", optimize=True)
print("mean", img.mean(), "min", img.min(), "max", img.max())
