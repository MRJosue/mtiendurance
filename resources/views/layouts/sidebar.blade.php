<div x-data="{ sidebarOpen: false }" class="relative z-50">

    <!-- BOTÓN HAMBURGUESA (móvil) -->
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button @click="sidebarOpen = true"
                class="p-2 bg-gray-900 text-white rounded focus:outline-none focus:ring">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>

    <!-- MENÚ LATERAL -->
    <div
        :class="{ 'translate-x-0': sidebarOpen || window.innerWidth >= 1024, '-translate-x-full': !sidebarOpen && window.innerWidth < 1024 }"
        class="fixed top-0 left-0 h-screen w-64 bg-white border-r border-gray-200 shadow transform transition-transform duration-300 ease-in-out z-40 overflow-y-auto"
        @resize.window="sidebarOpen = window.innerWidth >= 1024"
        style="will-change: transform"
    >
        <!-- BOTÓN CERRAR -->
        <div class="absolute top-2 right-2 lg:hidden">
            <button @click="sidebarOpen = false"
                    class="p-1 text-gray-700 hover:text-red-500 focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Contenido del menú -->
        <div class="p-4 space-y-4">
            <div class="text-lg font-bold text-gray-700">Menú Rápido</div>
            <ul class="space-y-2">
                <li><a href="#seccion1" class="block px-4 py-2 rounded hover:bg-gray-100 text-gray-700">Sección 1</a></li>
                <li><a href="#seccion2" class="block px-4 py-2 rounded hover:bg-gray-100 text-gray-700">Sección 2</a></li>
                <li><a href="#seccion3" class="block px-4 py-2 rounded hover:bg-gray-100 text-gray-700">Sección 3</a></li>
            </ul>
        </div>
    </div>

    <!-- FONDO OSCURO EN MÓVIL -->
    <div
        x-show="sidebarOpen && window.innerWidth < 1024"
        @click="sidebarOpen = false"
        x-transition.opacity
        class="fixed inset-0 bg-black bg-opacity-50 z-30"
        x-cloak
    ></div>
</div>
