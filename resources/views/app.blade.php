<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" data-scheme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#eef2f6">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">

        {{--
            Apply the saved theme before the page paints so it never flashes, and resolve it into
            data-scheme (light / dark) whenever the choice or the device setting changes.
            Keep in sync with useAppearance.
        --}}
        <script>
            (() => {
                const root = document.documentElement;
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');

                try {
                    const appearance = localStorage.getItem('appearance');

                    if (appearance === 'dark' || appearance === 'system') {
                        root.dataset.theme = appearance;
                    }
                } catch {}

                const resolve = () => {
                    const theme = root.dataset.theme;
                    const scheme = theme === 'dark' || (theme === 'system' && prefersDark.matches) ? 'dark' : 'light';

                    root.dataset.scheme = scheme;
                    document.querySelector('meta[name="theme-color"]')
                        ?.setAttribute('content', scheme === 'dark' ? '#070a0e' : '#eef2f6');
                };

                resolve();
                prefersDark.addEventListener('change', resolve);
                new MutationObserver(resolve).observe(root, { attributes: true, attributeFilter: ['data-theme'] });
            })();
        </script>

        <link rel="icon" href="/favicon.ico" sizes="32x32">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="manifest" href="/manifest.webmanifest">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
