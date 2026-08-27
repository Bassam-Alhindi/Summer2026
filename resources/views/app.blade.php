<!DOCTYPE html>
<html lang="{{ $htmlLang ?? 'ar' }}" dir="{{ $locale ?? 'rtl' }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <meta property="og:type" content="website" />
        <meta property="og:title" content="MyWallet | لكل ريال وجهة... ولكل هدف وصول" />
        <meta property="og:description" content="محفظتك الذكية لتتبع مصاريفك ودخلك بكل سهولة ووضوح — لكل ريال وجهة." />
        <meta property="og:image" content="https://mywallet-app.up.railway.app/og-banner.png" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:url" content="https://mywallet-app.up.railway.app/" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="MyWallet | لكل ريال وجهة... ولكل هدف وصول" />
        <meta name="twitter:description" content="محفظتك الذكية لتتبع مصاريفك ودخلك بكل سهولة ووضوح — لكل ريال وجهة." />
        <meta name="twitter:image" content="https://mywallet-app.up.railway.app/og-banner.png" />

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <x-inertia::head>
            <title>MyWallet</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
