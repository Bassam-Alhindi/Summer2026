<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $locale ?? 'ltr' }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <meta property="og:type" content="website">
        <meta property="og:title" content="MyWallet | لكل ريال وجهة">
        <meta property="og:description" content="تتبع مصاريفك ودخلك بذكاء — A smart expense tracker giving every riyal a purpose.">
        <meta property="og:image" content="{{ asset('logo.png') }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="MyWallet | لكل ريال وجهة">
        <meta name="twitter:description" content="تتبع مصاريفك ودخلك بذكاء — A smart expense tracker giving every riyal a purpose.">
        <meta name="twitter:image" content="{{ asset('logo.png') }}">

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
