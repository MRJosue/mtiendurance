

<aside
    x-show="sidebarOpen"
    @click.outside="sidebarOpen = window.innerWidth >= 1024"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full opacity-0"
    x-transition:enter-end="translate-x-0 opacity-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="translate-x-0 opacity-100"
    x-transition:leave-end="-translate-x-full opacity-0"
    x-cloak
    class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 text-white h-screen overflow-y-auto"
>
    <!-- BOTÓN CERRAR (siempre visible mientras sidebarOpen sea true) -->
    <div class="absolute top-2 right-2 z-50">
            <button
                @click="sidebarOpen = false; sidebarForced = true"
                class="p-1 rounded-md text-white bg-gray-800 hover:bg-gray-700"
                aria-label="Cerrar menú"
            >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <div>

        <!-- Logo -->
    <div class="h-16 flex items-center justify-center border-b border-gray-700">
        <a href="{{ route('dashboard') }}" class="text-lg font-bold">
            MTI endurance
        </a>
    </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-2 text-sm">
            <!-- Dashboard -->
            @can('asidedashboard')
            <a href="{{ route('dashboard') }}"
            class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('dashboard') ? 'bg-gray-800' : '' }}">
                Dashboard
            </a>
            @endcan


            <!-- Pre-Proyectos -->
            @can('asidepreproyectos')
            <a href="{{ route('preproyectos.index') }}"
            class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('preproyectos.index') ? 'bg-gray-800' : '' }}">
                Pre Proyectos
            </a>
            @endcan
            <!-- Proyectos -->
            @can('asideproyectos')
            <a href="{{ route('proyectos.index') }}"
            class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('proyectos.index') ? 'bg-gray-800' : '' }}">
                Proyectos
            </a>
            @endcan

            @can('asidediseniodesplegable')
            <!-- Diseño -->
                <div>
                    <button
                        @click="openSections['disenio'] = !openSections['disenio']"
                        class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                        :class="openSections['disenio'] ? 'underline text-blue-400' : ''"
                    >
                        <span>Diseño</span>

                        <!-- Flecha -->
                        <svg
                            :class="openSections['disenio'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                            class="w-4 h-4 transition-transform duration-200"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div x-show="openSections['disenio']" x-transition class="pl-6 mt-1 space-y-1">
                        @can('asidediseniodesplegableTareas')
                        <a href="{{ route('disenio.admin_tarea') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Tareas Diseño</a>
                        @endcan
                         @can('asidediseniodesplegableAdminTareas ')
                        <a href="{{ route('disenio.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Diseño</a>
                        @endcan
                    </div>
                </div>
            @endcan
            @can('asideproducciondesplegable')
            <!-- Producción -->
                <div>
                    <button
                        @click="openSections['produccion'] = !openSections['produccion']"
                        class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                        :class="openSections['produccion'] ? 'underline text-blue-400' : ''"
                    >
                        <span>Producción</span>
                        <svg :class="openSections['produccion'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                            class="w-4 h-4 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="openSections['produccion']" x-transition class="pl-6 mt-1 space-y-1">
                        <a href="{{ route('produccion.tareas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Tareas Producción</a>
                        <a href="{{ route('programacion.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Programación</a>
                        <a href="{{ route('produccion.ordenes_produccion') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Órdenes de Producción</a>
                    </div>
                </div>
            @endcan 
            <!-- Catálogos -->
            @hasanyrole('admin')
            <div>
                <button
                    @click="openSections['catalogos'] = !openSections['catalogos']"
                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                    :class="openSections['catalogos'] ? 'underline text-blue-400' : ''"
                >
                    <span>Envío</span>
                    <svg :class="openSections['catalogos'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                        class="w-4 h-4 transition-transform duration-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openSections['catalogos']" x-transition class="pl-6 mt-1 space-y-1">
                    <a href="{{ route('catalogos.paises.index') }}" class="block px-2 py-1 hover:bg-gray-800">Países</a>
                    <a href="{{ route('catalogos.estados.index') }}" class="block px-2 py-1 hover:bg-gray-800">Estados</a>
                    <a href="{{ route('catalogos.ciudades.index') }}" class="block px-2 py-1 hover:bg-gray-800">Ciudades</a>
                    <a href="{{ route('catalogos.tipoenvio.index') }}" class="block px-2 py-1 hover:bg-gray-800">Tipos de envío</a>
                </div>
            </div>
            @endhasanyrole

            <!-- Productos -->
            @hasanyrole('admin')    
                <div>
                    <button
                        @click="openSections['productos'] = !openSections['productos']"
                        class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                        :class="openSections['productos'] ? 'underline text-blue-400' : ''"
                    >
                        <span>Productos</span>
                        <svg :class="openSections['productos'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                            class="w-4 h-4 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="openSections['productos']" x-transition class="pl-6 mt-1 space-y-1">
                        <a href="{{ route('catalogos.categorias.index') }}" class="block px-2 py-1 hover:bg-gray-800">Categorías</a>
                        <a href="{{ route('catalogos.producto.index') }}" class="block px-2 py-1 hover:bg-gray-800">Producto</a>
                        <a href="{{ route('catalogos.caracteristica.index') }}" class="block px-2 py-1 hover:bg-gray-800">Características</a>
                        <a href="{{ route('catalogos.opciones.index') }}" class="block px-2 py-1 hover:bg-gray-800">Opciones</a>
                        <a href="{{ route('catalogos.producto.layout') }}" class="block px-2 py-1 hover:bg-gray-800">Layout</a>
                        <a href="{{ route('catalogos.tallas.tallas') }}" class="block px-2 py-1 hover:bg-gray-800">Tallas</a>
                        <a href="{{ route('catalogos.tallas.grupos') }}" class="block px-2 py-1 hover:bg-gray-800">Grupos</a>
                    </div>
                </div>
            @endhasanyrole

            @can('asideusuariosdesplegable')
            <!-- Usuarios -->
            <div>
                <button
                    @click="openSections['usuarios'] = !openSections['usuarios']"
                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                    :class="openSections['usuarios'] ? 'underline text-blue-400' : ''"
                >
                    <span>Usuarios</span>
                    <svg :class="openSections['usuarios'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                        class="w-4 h-4 transition-transform duration-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openSections['usuarios']" x-transition class="pl-6 mt-1 space-y-1">
                    <a href="{{ route('usuarios.index') }}" class="block px-2 py-1 hover:bg-gray-800">Usuarios</a>
                    <a href="{{ route('permisos.index') }}" class="block px-2 py-1 hover:bg-gray-800">Permisos</a>
                </div>
            </div>
            @endcan
            <!-- Perfil -->
            <div class="mt-6 border-t border-gray-700 pt-4 px-4">
                <div class="text-sm">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-400">{{ Auth::user()->email }}</div>
                <a href="{{ route('profile.edit') }}" class="block mt-2 text-blue-400 hover:underline text-sm">Editar Perfil</a>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="text-red-400 hover:underline text-sm">Cerrar sesión</button>
                </form>
            </div>


            
        </nav>
    </div>
</aside>


