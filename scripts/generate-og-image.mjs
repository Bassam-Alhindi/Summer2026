/**
 * Renders resources/og/og-image.svg to public/og-image.png (1200x630) for
 * social link previews.
 *
 * The Arabic tagline needs real shaping, so the font is vendored in the repo
 * and loaded explicitly - the deploy image has no system Arabic font and
 * would otherwise render blank boxes.
 *
 * Runs as part of `npm run build`. The generated PNG is committed, so if
 * rendering ever fails on a build host we keep the committed copy and let the
 * build continue rather than failing a deploy over a preview image.
 */
import { Resvg } from '@resvg/resvg-js';
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const svgPath = resolve(root, 'resources/og/og-image.svg');
const fontPath = resolve(root, 'resources/fonts/Cairo.ttf');
const outPath = resolve(root, 'public/og-image.png');

const WIDTH = 1200;
const HEIGHT = 630;

function bail(message) {
    console.error(`[og-image] ${message}`);
    if (existsSync(outPath)) {
        console.error('[og-image] keeping the committed public/og-image.png and continuing');
        process.exit(0);
    }
    console.error('[og-image] no existing public/og-image.png to fall back to');
    process.exit(1);
}

try {
    for (const [label, p] of [['SVG', svgPath], ['font', fontPath]]) {
        if (!existsSync(p)) bail(`missing ${label}: ${p}`);
    }

    const resvg = new Resvg(readFileSync(svgPath, 'utf8'), {
        background: '#07070b',
        fitTo: { mode: 'width', value: WIDTH },
        font: {
            fontFiles: [fontPath],
            loadSystemFonts: false, // keep output identical on every machine
            defaultFontFamily: 'Cairo',
        },
    });

    const png = resvg.render();
    if (png.width !== WIDTH || png.height !== HEIGHT) {
        bail(`expected ${WIDTH}x${HEIGHT}, got ${png.width}x${png.height}`);
    }

    const buf = png.asPng();
    writeFileSync(outPath, buf);
    console.log(`[og-image] wrote public/og-image.png (${png.width}x${png.height}, ${(buf.length / 1024).toFixed(1)} KB)`);
} catch (err) {
    bail(`render failed: ${err?.message ?? err}`);
}
