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
    {{-- ASIDE (incluye su propia lógica Alpine) --}}
    @include('layouts.aside')

    {{-- CONTENIDO PRINCIPAL: padding dinámico segun estado del aside --}}
    <div
        x-data="{ asideOpen: true }"
        @aside:state.window="asideOpen = $event.detail.open"
        :class="asideOpen ? 'lg:pl-64' : 'lg:pl-0'"
        class="min-h-screen flex flex-col relative transition-[padding] duration-300 ease-in-out"
    >
        <div class="absolute top-4 right-4 z-40">
            @livewire('notificaciones.notificaciones')
        </div>

        @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="flex-1">
            {{ $slot }}
        </main>

        <x-boton-navegacion />
        @livewire('cambiar-rol-actual')
    </div>

    @livewireScripts
    @stack('scripts')
    <x-notify::notify />
    @notifyJs

    <style>[x-cloak]{ display:none !important; }</style>
</body>

</html>
