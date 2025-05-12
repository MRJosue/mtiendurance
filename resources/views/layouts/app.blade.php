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

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       
       
                   @vite(['resources/css/app.css', 'resources/js/app.js'])
     

        @livewireStyles


        {{-- @notifyCss Esta sentencia produce errores por el llamado de alphine--}}
    </head>
        <body class="font-sans antialiased">

            <div x-data="{ sidebarOpen: window.innerWidth >= 1024, openSections: {} }"
                @resize.window="sidebarOpen = window.innerWidth >= 1024"
                class="min-h-screen flex bg-gray-100 dark:bg-gray-900 relative">

                <!-- Botón hamburguesa (solo en móvil) -->
                <!-- BOTÓN HAMBURGUESA -->
                <button
                    @click="sidebarOpen = true"
                    x-show="!sidebarOpen"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-4"
                    x-cloak
                    class="fixed top-4 left-4 z-50 p-2 bg-gray-800 text-white rounded-md focus:outline-none"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Sidebar -->
                <div
                    x-show="sidebarOpen"
                    @click.outside="sidebarOpen = window.innerWidth >= 1024"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="-translate-x-full opacity-0"
                    x-transition:enter-end="translate-x-0 opacity-100"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="translate-x-0 opacity-100"
                    x-transition:leave-end="-translate-x-full opacity-0"
                    x-cloak
                    class="fixed inset-y-0 left-0 z-40 w-64 transform bg-gray-900 text-white overflow-y-auto lg:static lg:translate-x-0 lg:opacity-100"
                    style="will-change: transform"
                >
                   
                    @include('layouts.aside')
                </div>

    
                <!-- CONTENIDO PRINCIPAL -->
                <div
           
                    class="flex-1 transition-all duration-300 ease-in-out min-h-screen"
                >

                <div>
                     @include('layouts.navigation')
                </div>
                    @if (isset($header))
                        <header class="bg-white dark:bg-gray-800 shadow">
                            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endif

                    <main   >
                        {{ $slot }}
                    </main>
                </div>

                         @livewire('cambiar-rol-actual')
            </div>

            <livewire:scripts />
            @stack('scripts')

            <x-notify::notify />
            @notifyJs

        </body>

</html>


