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
    

    <!-- Vite Assets -->
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    {{-- @vite('resources/js/app.js') --}}
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
    

     @wireUiStyles
    {{-- <script src="https://cdn.jsdelivr.net/npm/@wireui/scripts@1.0.0/dist/index.umd.js"></script> --}}
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900"
      x-data="{ sidebarOpen: window.innerWidth >= 1024, sidebarForced: false, openSections: {} }"
    
    
      @resize.window="
                if (!sidebarForced) {
                    sidebarOpen = window.innerWidth >= 1024
                }"
      @resize.window="sidebarOpen = window.innerWidth >= 1024">

        <!-- Botón hamburguesa (solo visible en móvil cuando sidebar está cerrado) -->
        <button
            @click="sidebarOpen = true; sidebarForced = true"
            x-show="!sidebarOpen"
            x-transition
            x-cloak
            class="fixed top-4 left-4 z-40 p-2 bg-gray-800 text-white rounded-md focus:outline-none"
        >

            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Layout principal -->
        <div class="min-h-screen flex flex-row">

            <!-- Sidebar -->
            <aside
                x-show="sidebarOpen"
                @click.outside="sidebarOpen = window.innerWidth >= 1024"
                x-transition:enter="transition ease-out duration-300"
                x-transition:leave="transition ease-in duration-300"
                x-cloak
                class="w-64 bg-gray-900 text-white flex flex-col h-screen overflow-y-auto lg:block"
            >
                <!-- Botón cerrar solo visible en móvil -->
                <div class="flex justify-end p-2 lg:hidden">
                    <button
                        @click="sidebarOpen = false"
                        class="p-1 rounded-md text-white bg-gray-800 hover:bg-gray-700"
                        aria-label="Cerrar menú">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @include('layouts.aside')
            </aside>

            <!-- Contenido principal -->
            <div
                    class="flex-1 min-h-screen flex flex-col relative transition-all duration-300 ease-in-out"
                    :class="{ 'ml-4': sidebarOpen && window.innerWidth >= 1024 }"
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
                <div class="p-4">
                    <x-boton-navegacion />
                </div>

                @livewire('cambiar-rol-actual')
            </div>


            
        </div>
        @wireUiScripts
        @livewireScripts
        @stack('scripts')
        <x-notify::notify />
        
        

        @notifyJs

</body>

</html>
