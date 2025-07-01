

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
                Solicitudes de Proyectos
            </a>
            @endcan


            <!-- Proyectos -->
            @can('asideproyectos')
            <a href="{{ route('proyectos.index') }}"
            class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('proyectos.index') ? 'bg-gray-800' : '' }}">
                Diseños
            </a>
            @endcan


            <!-- Pedidos -->
         
            <a href="{{ route('pedidos.index') }}"
            class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('pedidos.index') ? 'bg-gray-800' : '' }}">
                Pedidos
            </a>
        

            {{-- diseño --}}
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
                        {{-- @can('asidediseniodesplegableAdminTareas ') --}}
                        <a href="{{ route('disenio.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Administrador de Diseño</a>
                        {{-- @endcan --}}
                        {{-- @can('asidediseniodesplegableTareas') --}}
                        <a href="{{ route('disenio.admin_tarea') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Tareas Diseño</a>
                        {{-- @endcan --}}

                    </div>
                </div>
            @endcan
            
            {{-- Administraciondemuestras --}}
            <div>
                    <button
                        @click="openSections['Administraciondemuestras'] = !openSections['Administraciondemuestras']"
                        class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                        :class="openSections['Administraciondemuestras'] ? 'underline text-blue-400' : ''"
                    >
                        <span>Administracion de muestras</span>
                        <svg :class="openSections['Administraciondemuestras'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                            class="w-4 h-4 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="openSections['Administraciondemuestras']" x-transition class="pl-6 mt-1 space-y-1">
                        <a href="{{ route('produccion.tareas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Tareas Producción</a>
                    </div>
            </div>

            {{-- AdministracionPedidos --}}
            <div>
                    <button
                        @click="openSections['AdministracionPedidos'] = !openSections['AdministracionPedidos']"
                        class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                        :class="openSections['AdministracionPedidos'] ? 'underline text-blue-400' : ''"
                    >
                        <span>Administracion de Pedidos</span>
                        <svg :class="openSections['AdministracionPedidos'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                            class="w-4 h-4 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="openSections['AdministracionPedidos']" x-transition class="pl-6 mt-1 space-y-1">
                        <a href="{{ route('produccion.tareas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Administracion de Pedidos</a>
                    </div>
            </div>

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

            {{-- Entregas --}}
            <div>
                    <button
                        @click="openSections['Entregas'] = !openSections['Entregas']"
                        class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                        :class="openSections['Entregas'] ? 'underline text-blue-400' : ''"
                    >
                        <span>Entregas</span>
                        <svg :class="openSections['Entregas'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                            class="w-4 h-4 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="openSections['Entregas']" x-transition class="pl-6 mt-1 space-y-1">
                        <a href="{{ route('produccion.tareas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Administracion de Pedidos</a>
                    </div>
            </div>
            

            {{-- Facturacion --}}
            <div>
                    <button
                        @click="openSections['Facturacion'] = !openSections['Facturacion']"
                        class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                        :class="openSections['Facturacion'] ? 'underline text-blue-400' : ''"
                    >
                        <span>Facturacion</span>
                        <svg :class="openSections['Facturacion'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                            class="w-4 h-4 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="openSections['Facturacion']" x-transition class="pl-6 mt-1 space-y-1">
                        <a href="{{ route('produccion.tareas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Administracion de Pedidos</a>
                    </div>
            </div>
            
            
        
            @hasanyrole('admin')
             <!-- Configuración (agrupa Envío, Productos y Usuarios) -->
             <div>
                    <button
                        @click="openSections['configuracion'] = !openSections['configuracion']"
                        class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                        :class="openSections['configuracion'] ? 'underline text-blue-400' : ''"
                    >
                        <span>Configuración</span>
                        <svg
                            :class="openSections['configuracion'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                            class="w-4 h-4 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="openSections['configuracion']" x-transition class="pl-6 mt-1 space-y-2">
                        
                        <!-- Envío -->
                        <div>
                            <button
                                @click="openSections['config_envio'] = !openSections['config_envio']"
                                class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                                :class="openSections['config_envio'] ? 'underline text-blue-400' : ''"
                            >
                                <span>Envío</span>
                                <svg
                                    :class="openSections['config_envio'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                                    class="w-4 h-4 transition-transform duration-200"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            <div x-show="openSections['config_envio']" x-transition class="pl-6 mt-1 space-y-1">
                                <a href="{{ route('catalogos.paises.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Países</a>
                                <a href="{{ route('catalogos.estados.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Estados</a>
                                <a href="{{ route('catalogos.ciudades.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Ciudades</a>
                                <a href="{{ route('catalogos.tipoenvio.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Tipos de envío</a>
                            </div>
                        </div>

                        <!-- Productos -->
                        <div>
                            <button
                                @click="openSections['config_productos'] = !openSections['config_productos']"
                                class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                                :class="openSections['config_productos'] ? 'underline text-blue-400' : ''"
                            >
                                <span>Productos</span>
                                <svg
                                    :class="openSections['config_productos'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                                    class="w-4 h-4 transition-transform duration-200"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            <div x-show="openSections['config_productos']" x-transition class="pl-6 mt-1 space-y-1">
                                <a href="{{ route('catalogos.categorias.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Categorías</a>
                                <a href="{{ route('catalogos.producto.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Productos</a>
                                <a href="{{ route('catalogos.caracteristica.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Características</a>
                                <a href="{{ route('catalogos.opciones.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Opciones</a>
                                <a href="{{ route('catalogos.producto.layout') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Layout</a>
                                <a href="{{ route('catalogos.tallas.tallas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Tallas</a>
                                <a href="{{ route('catalogos.tallas.grupos') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Grupos</a>
                            </div>
                        </div>

                        <!-- Usuarios -->
                        @can('asideusuariosdesplegable')
                        <div>
                            <button
                                @click="openSections['config_usuarios'] = !openSections['config_usuarios']"
                                class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                                :class="openSections['config_usuarios'] ? 'underline text-blue-400' : ''"
                            >
                                <span>Usuarios</span>
                                <svg
                                    :class="openSections['config_usuarios'] ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                                    class="w-4 h-4 transition-transform duration-200"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            <div x-show="openSections['config_usuarios']" x-transition class="pl-6 mt-1 space-y-1">
                                <a href="{{ route('usuarios.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Usuarios</a>
                                <a href="{{ route('permisos.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Permisos</a>
                            </div>
                        </div>
                        @endcan
                    </div>
             </div>
            @endhasanyrole



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


