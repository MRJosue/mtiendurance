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
                <div class="flex items-center gap-2 sm:gap-3">
                    {{-- Campana de notificaciones --}}
                    <div class="relative">
                        @livewire('notificaciones.notificaciones')
                    </div>

                    {{-- Dropdown de idioma --}}
                    <div x-data="{openLang:false}" class="relative">
                        <button
                            @click="openLang = !openLang"
                            class="inline-flex items-center gap-2 rounded-md bg-white/90 px-3 py-2 text-sm font-medium text-gray-700 shadow hover:bg-white
                                dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                            aria-label="{{ __('menu.open_menu') }}"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3a9 9 0 100 18 9 9 0 000-18zm0 0c2.5 2.5 2.5 15.5 0 18m0-18C9.5 5.5 9.5 18.5 12 21m-9-9h18" />
                            </svg>
                            <span class="uppercase leading-none">{{ app()->getLocale() }}</span>
                            <svg class="h-4 w-4 opacity-70" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        {{-- Menú de idioma --}}
                        <div
                            x-cloak
                            x-show="openLang"
                            @click.outside="openLang = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            class="absolute right-0 mt-2 w-56 rounded-md bg-white shadow-lg ring-1 ring-black/5 dark:bg-gray-800"
                        >
                            <div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-300">{{ __('Idioma') }}</div>
                            <a href="{{ route('lang.switch','es') }}" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                Español (ES)
                            </a>
                            <a href="{{ route('lang.switch','en') }}" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                English (EN)
                            </a>
                        </div>
                    </div>
                </div>
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
