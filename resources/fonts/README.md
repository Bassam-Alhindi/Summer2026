# Vendored fonts

## Cairo.ttf

Used only at build time by `scripts/generate-og-image.mjs` to render
`public/og-image.png`. It is **not** served to browsers - the app's web fonts
are handled separately by the `@fonts` directive.

It is vendored rather than resolved from the system because the deploy image
(`php:8.4-cli`) ships no Arabic font, so the Arabic tagline would render as
blank boxes. Loading it explicitly also keeps the output byte-identical across
machines.

- Family: Cairo (variable: slant, weight), covers Arabic + Latin
- Source: https://github.com/google/fonts/tree/main/ofl/cairo
- License: SIL Open Font License 1.1 - see `OFL.txt`
