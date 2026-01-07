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

    
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    @vite(['resources/css/app.css','resources/js/app.js'])

    @livewireStyles
    @wireUiScripts
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 overflow-x-hidden">
    <div class="min-h-screen flex flex-col relative">

        {{-- TOPBAR mínima (misma UI que el dashboard) --}}
        @include('layouts.partials.topbar')

        <main class="flex-1 flex items-center justify-center ">
            <div class="w-full max-w-5xl">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
    <x-notify::notify />
    @notifyJs

    <style>[x-cloak]{ display:none !important; }</style>
</body>

</html>
