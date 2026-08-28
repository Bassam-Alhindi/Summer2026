import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

const isSvelteCheck = process.argv.some((argument) => argument.includes('svelte-check'));

if (isSvelteCheck) {
    process.env.LARAVEL_BYPASS_ENV_CHECK ??= '1';
}

export default defineConfig({
    // svelte-sonner ships raw .svelte source written in Svelte 4 legacy syntax ($:, export let).
    // If Vite pre-bundles it, its compiled output pulls in its own copy of the Svelte runtime,
    // so `legacy_mode_flag` gets enabled in one runtime instance while component contexts are
    // pushed from another. The legacy context (`context.l`) is then null and hydration dies with
    // "Cannot read properties of null (reading '$')". Excluding it keeps it in the app's own
    // module graph; deduping svelte guarantees exactly one runtime instance.
    resolve: {
        dedupe: ['svelte', 'svelte/internal', 'svelte/internal/client'],
    },
    optimizeDeps: {
        exclude: ['svelte-sonner'],
        // Pre-bundle the icon packs up front: discovering icon subpaths lazily forces repeated
        // re-optimization passes, and each pass hands the browser a new dep generation.
        include: ['lucide-svelte', '@lucide/svelte'],
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        tailwindcss(),
        svelte(),
        wayfinder({
            formVariants: true,
        }),
    ],
});
