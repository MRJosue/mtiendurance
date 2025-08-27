{{-- resources/views/components/aside-menu.blade.php --}}
<div x-data="asideMenu()" x-init="init()" x-cloak @resize.window="onResize()">

    {{-- BOTÓN HAMBURGUESA (siempre disponible cuando sidebarOpen = false) --}}
    <button
        x-show="!sidebarOpen"
        @click="open()"
        class="fixed top-3 left-3 z-50 p-2 rounded-md bg-gray-900 text-white shadow"  {{-- OJO: sin lg:hidden y con z-50 --}}
        aria-label="Abrir menú"
    >
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    {{-- ASIDE --}}
    <aside
        x-show="sidebarOpen"
        @click.outside="onOutsideClick()"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="-translate-x-full opacity-0"
        class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 text-white h-screen overflow-y-auto"
    >
        {{-- Botón Cerrar / Pin --}}
        <div class="absolute top-2 right-2 z-50 flex items-center space-x-1">
            {{-- “Pinear” en escritorio: recuerda abierto aun con resize (se guarda en localStorage) --}}
            {{-- <button
                @click="togglePin()"
                class="p-1 rounded-md text-white bg-gray-800 hover:bg-gray-700"
                :title="sidebarPinned ? 'Desanclar' : 'Anclar'"
                aria-label="Anclar/Desanclar menú"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7l-1-5M8 7l1-5m8 16l4 4m-6-6l-6 6m2-10l2 2m-6 4l-2 2"/>
                </svg>
            </button> --}}

            <button
                @click="close()"
                class="p-1 rounded-md text-white bg-gray-800 hover:bg-gray-700"
                aria-label="Cerrar menú"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Logo --}}
        <div class="h-16 flex items-center justify-center border-b border-gray-700">
            <a href="{{ route('dashboard') }}" class="text-lg font-bold block px-4 py-2 rounded hover:bg-gray-800">
                MTI endurance
            </a>
        </div>

        {{-- Navegación --}}
        <nav class="p-4 space-y-2 text-sm">
            @can('asidedashboard')
            <a href="{{ route('dashboard') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Dashboard</a>
            @endcan

            @can('asidepreproyectos')
            <a href="{{ route('preproyectos.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                Solicitudes de Proyectos
            </a>
            @endcan

            @can('asideproyectos')
            <a href="{{ route('proyectos.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Diseños</a>
            @endcan

            <a href="{{ route('pedidos.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Pedidos</a>

            {{-- Diseño --}}
            @can('asidediseniodesplegable')
            <div>
                <button
                    @click="toggleSection('disenio')"
                    :class="openSections.disenio ? 'underline text-blue-400' : ''"
                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                >
                    <span>Diseño</span>
                    <svg :class="openSections.disenio ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                         class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openSections.disenio" x-transition class="pl-6 mt-1 space-y-1">
                    <a href="{{ route('disenio.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        Administrador de Diseño
                    </a>
                    <a href="{{ route('disenio.admin_tarea') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        Tareas Diseño
                    </a>

                                        
                    @can('asideAdministraciónMuestras')
                        <a href="{{ route('produccion.adminmuestras') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Administración Muestras</a>
                    @endcan
                </div>
            </div>
            @endcan

            {{-- Producción --}}
            @can('asideproducciondesplegable')
            <div>
                <button
                    @click="toggleSection('produccion')"
                    :class="openSections.produccion ? 'underline text-blue-400' : ''"
                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                >
                    <span>Producción</span>
                    <svg :class="openSections.produccion ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                         class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <div x-show="openSections.produccion" x-transition class="pl-6 mt-1 space-y-1">

                    @can('asideAprobacionesEspeciales')
                        <a href="{{ route('produccion.aprobacion_especial') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Aprobaciones Especiales</a>
                    @endcan

                    
                    @can('asideAdministraciónPedidos')
                         <a href="{{ route('produccion.adminpedidos') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Administración Pedidos</a>
                    @endcan
                   
                    <a href="{{ route('produccion.tareas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Tareas Produccion</a>
                    <a href="{{ route('produccion.corte') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Corte</a>
                    <a href="{{ route('produccion.sublimado') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Sublimado</a>
                    <a href="{{ route('produccion.costura') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Costura</a>
                    <a href="{{ route('produccion.maquila') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Maquila</a>
                    <a href="{{ route('produccion.facturacion') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Facturacion</a>
                    <a href="{{ route('produccion.entrega') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Entrega</a>
                    <a href="{{ route('produccion.ordenes_produccion') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Órdenes de Producción</a>

                    {{-- recorremos los grupos  --}}
                </div>
            </div>
            @endcan

            {{-- Entregas --}}
            <div>
                <button
                    @click="toggleSection('entregas')"
                    :class="openSections.entregas ? 'underline text-blue-400' : ''"
                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                >
                    <span>Entregas</span>
                    <svg :class="openSections.entregas ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                         class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openSections.entregas" x-transition class="pl-6 mt-1 space-y-1">
                    <a href="{{ route('produccion.tareas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        Administración de Entregas
                    </a>
                </div>
            </div>

            {{-- Facturación --}}
            <div>
                <button
                    @click="toggleSection('facturacion')"
                    :class="openSections.facturacion ? 'underline text-blue-400' : ''"
                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                >
                    <span>Facturación</span>
                    <svg :class="openSections.facturacion ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                         class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openSections.facturacion" x-transition class="pl-6 mt-1 space-y-1">
                    <a href="{{ route('produccion.tareas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        Administración de Facturación
                    </a>
                </div>
            </div>

            @hasanyrole('admin')
            {{-- Configuración --}}
            <div>
                <button
                    @click="toggleSection('configuracion')"
                    :class="openSections.configuracion ? 'underline text-blue-400' : ''"
                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                >
                    <span>Configuración</span>
                    <svg :class="openSections.configuracion ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                         class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <div x-show="openSections.configuracion" x-transition class="pl-6 mt-1 space-y-2">
                    {{-- Envío --}}
                    <div>
                        <button
                            @click="toggleSection('config_envio')"
                            :class="openSections.config_envio ? 'underline text-blue-400' : ''"
                            class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                        >
                            <span>Envío</span>
                            <svg :class="openSections.config_envio ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                                 class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div x-show="openSections.config_envio" x-transition class="pl-6 mt-1 space-y-1">
                            <a href="{{ route('catalogos.paises.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Países</a>
                            <a href="{{ route('catalogos.estados.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Estados</a>
                            <a href="{{ route('catalogos.ciudades.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Ciudades</a>
                            <a href="{{ route('catalogos.tipoenvio.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Tipos de envío</a>
                        </div>
                    </div>

                    {{-- Productos --}}
                    <div>
                        <button
                            @click="toggleSection('config_productos')"
                            :class="openSections.config_productos ? 'underline text-blue-400' : ''"
                            class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                        >
                            <span>Productos</span>
                            <svg :class="openSections.config_productos ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                                 class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div x-show="openSections.config_productos" x-transition class="pl-6 mt-1 space-y-1">
                            <a href="{{ route('catalogos.categorias.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Categorías</a>
                            <a href="{{ route('catalogos.producto.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Productos</a>
                            <a href="{{ route('catalogos.caracteristica.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Características</a>
                            <a href="{{ route('catalogos.opciones.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Opciones</a>
                            <a href="{{ route('catalogos.producto.layout') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Layout</a>
                            <a href="{{ route('catalogos.tallas.tallas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Tallas</a>
                            <a href="{{ route('catalogos.tallas.grupos') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Grupos</a>
                            <a href="{{ route('catalogos.flujoProduccion') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Flujo De Produccion</a>
                            <a href="{{ route('catalogos.flujoFiltrosProduccion') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Filtros Produccion</a>
                        </div>
                    </div>

                    {{-- Usuarios --}}
                    @can('asideusuariosdesplegable')
                    <div>
                        <button
                            @click="toggleSection('config_usuarios')"
                            :class="openSections.config_usuarios ? 'underline text-blue-400' : ''"
                            class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                        >
                            <span>Usuarios</span>
                            <svg :class="openSections.config_usuarios ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                                 class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div x-show="openSections.config_usuarios" x-transition class="pl-6 mt-1 space-y-1">
                            <a href="{{ route('usuarios.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Usuarios</a>
                            <a href="{{ route('permisos.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">Permisos</a>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
            @endhasanyrole

            {{-- Perfil --}}
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
    </aside>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('asideMenu', () => ({
        // Estado
        sidebarOpen: false,
        sidebarPinned: JSON.parse(localStorage.getItem('sidebarPinned') || 'false'),
        openSections: JSON.parse(localStorage.getItem('openSections') || '{}'),
        selectedRoute: '{{ request()->route()->getName() }}',

        // Inicialización
        init() {
            // Abierto por defecto en escritorio, cerrado en móvil… salvo que esté “pineado”
            this.sidebarOpen = this.sidebarPinned || window.innerWidth >= 1024;
        },

        // Métodos de UI
        open() {
            this.sidebarOpen = true;
        },
        close() {
            this.sidebarOpen = false;
            // si cierra manual, desancla para que respete breakpoints
            this.sidebarPinned = false;
            localStorage.setItem('sidebarPinned', JSON.stringify(this.sidebarPinned));
        },
        togglePin() {
            this.sidebarPinned = !this.sidebarPinned;
            localStorage.setItem('sidebarPinned', JSON.stringify(this.sidebarPinned));
            if (!this.sidebarPinned && window.innerWidth < 1024) {
                this.sidebarOpen = false;
            } else if (this.sidebarPinned) {
                this.sidebarOpen = true;
            }
        },
        onOutsideClick() {
            // Cerrar con click afuera SOLO en móvil/tablet
            if (window.innerWidth < 1024 && !this.sidebarPinned) {
                this.sidebarOpen = false;
            }
        },
        onResize() {
            // Si no está “pineado”, seguir comportamiento responsive
            if (!this.sidebarPinned) {
                this.sidebarOpen = window.innerWidth >= 1024;
            }
        },

        // Submenús
        toggleSection(name) {
            this.openSections[name] = !this.openSections[name];
            localStorage.setItem('openSections', JSON.stringify(this.openSections));
        },
        isActive(route) {
            return this.selectedRoute === route;
        },


        sidebarOpen: false,
        // ...

        init() {
        this.sidebarOpen = this.sidebarPinned || window.innerWidth >= 1024;
        this.$nextTick(() => this.broadcast());
        this.$watch('sidebarOpen', () => this.broadcast());
        },

        open() { this.sidebarOpen = true; },
        close() { this.sidebarOpen = false; this.sidebarPinned = false; localStorage.setItem('sidebarPinned', 'false'); },

        onResize() { if (!this.sidebarPinned) this.sidebarOpen = window.innerWidth >= 1024; },

        broadcast() {
        window.dispatchEvent(new CustomEvent('aside:state', { detail: { open: this.sidebarOpen } }));
        },

    }));
});
</script>
