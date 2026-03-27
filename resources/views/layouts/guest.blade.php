<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <script>
            (() => {
                const theme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const shouldUseDark = theme ? theme === 'dark' : prefersDark;

                document.documentElement.classList.toggle('dark', shouldUseDark);
                document.documentElement.setAttribute('data-theme', shouldUseDark ? 'dark' : 'light');
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div class="absolute right-4 top-4">
                <button
                    type="button"
                    x-data
                    @click="$store.theme.toggle()"
                    class="inline-flex items-center gap-2 rounded-md bg-white/90 px-3 py-2 text-sm font-medium text-gray-700 shadow transition hover:bg-white dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    aria-label="Cambiar tema"
                >
                    <svg x-show="$store.theme.current === 'light'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.114 6.364-1.591-1.591M7.477 7.477 5.886 5.886m12.228 0-1.591 1.591M7.477 16.523l-1.591 1.591M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="$store.theme.current === 'dark'" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3c-.008.13-.01.26-.01.39A7.5 7.5 0 0018.61 10.8c.13 0 .26-.002.39-.01z" />
                    </svg>
                    <span x-text="$store.theme.current === 'dark' ? 'Oscuro' : 'Claro'"></span>
                </button>
            </div>

            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
