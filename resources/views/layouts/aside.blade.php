{{-- resources/views/components/aside-menu.blade.php --}}
<div x-data="asideMenu()" x-init="init()" x-cloak @resize.window="onResize()">

    {{-- BOTÓN HAMBURGUESA (siempre disponible cuando sidebarOpen = false) --}}
    <button
        x-show="!sidebarOpen"
        @click="open()"
        class="fixed top-3 left-3 z-50 p-2 rounded-md bg-gray-900 text-white shadow"
        aria-label="{{ __('menu.open_menu') }}"
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
        {{-- Botón Cerrar --}}
        <div class="absolute top-2 right-2 z-50 flex items-center space-x-1">
            <button
                @click="close()"
                class="p-1 rounded-md text-white bg-gray-800 hover:bg-gray-700"
                aria-label="{{ __('menu.close_menu') }}"
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
                {{ __('menu.brand') }}
            </a>
        </div>

        {{-- Navegación --}}
        <nav class="p-4 space-y-2 text-sm">



            @can('asidedashboard')
            <a href="{{ route('dashboard') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                {{ __('menu.dashboard') }}
            </a>
            @endcan

            @can('asidepreproyectos')
            <a href="{{ route('preproyectos.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                {{ __('menu.requests') }}
            </a>
            @endcan

            {{-- Diseño --}}
            @can('asidediseniodesplegable')
            <div>
                <button
                    @click="toggleSection('disenio')"
                    :class="openSections.disenio ? 'underline text-blue-400' : ''"
                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                >
                    <span>{{ __('menu.design') }}</span>
                    <svg :class="openSections.disenio ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                         class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>



                <div x-show="openSections.disenio" x-transition class="pl-6 mt-1 space-y-1">


                    @can('asidediseniodesplegable')
                        <a href="{{ route('proyectos.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                            {{ __('menu.designs') }}
                        </a>
                    @endcan
                    
                    @can('asidedisenioAdministracion')
                        <a href="{{ route('disenio.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                            {{ __('menu.design_admin') }}
                        </a>
                    @endcan

                    @can('asidedisenioTareas')
                        <a href="{{ route('disenio.admin_tarea') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                            {{ __('menu.design_tasks') }}
                        </a>
                    @endcan

                    @can('asideAdministraciónMuestras')
                        <a href="{{ route('produccion.adminmuestras') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                            {{ __('menu.samples_admin') }}
                        </a>
                    @endcan

                    @can('asideAdministraciónReconfiguracion')
                        <a href="{{ route('proyectos.reprogramar') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                            {{ __('menu.solicitudes_reconfiguracion') }}
                        </a>
                    @endcan

                    @livewire('aside-hojas', ['ubicacion' => 'diseño'])
                    
                </div>
            </div>
            @endcan

            {{-- Pedidos --}}
            
            <div>
                <button
                    @click="toggleSection('pedidos')"
                    :class="openSections.pedidos ? 'underline text-blue-400' : ''"
                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                >
                    <span>{{ __('menu.custom_orders') }}</span>
                    <svg :class="openSections.pedidos ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                         class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>

                </button>

                <div x-show="openSections.pedidos" x-transition class="pl-6 mt-1 space-y-1">

                    <a href="{{ route('pedidos.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        {{ __('menu.orders') }}
                    </a>

                       @livewire('aside-hojas', ['ubicacion' => 'pedidos'])
                </div>
            </div>


            {{-- Producción --}}
            @can('asideproducciondesplegable')
            <div>
                <button
                    @click="toggleSection('produccion')"
                    :class="openSections.produccion ? 'underline text-blue-400' : ''"
                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                >
                    <span>{{ __('menu.production') }}</span>
                    <svg :class="openSections.produccion ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                         class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <div x-show="openSections.produccion" x-transition class="pl-6 mt-1 space-y-1">
                    @can('asideAprobacionesEspeciales')
                        <a href="{{ route('produccion.aprobacion_especial') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                            {{ __('menu.special_approvals') }}
                        </a>
                    @endcan


                    @livewire('aside-hojas', ['ubicacion' => 'produccion'])


                    {{-- @can('asideAdministraciónPedidos')
                        <a href="{{ route('produccion.adminpedidos') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                            {{ __('menu.orders_admin') }}
                        </a>
                    @endcan --}}

                    {{-- <a href="{{ route('produccion.tareas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        {{ __('menu.prod_tasks') }}
                    </a>
                    <a href="{{ route('produccion.corte') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        {{ __('menu.cut') }}
                    </a>
                    <a href="{{ route('produccion.sublimado') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        {{ __('menu.sublimation') }}
                    </a>
                    <a href="{{ route('produccion.costura') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        {{ __('menu.sewing') }}
                    </a>
                    <a href="{{ route('produccion.maquila') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        {{ __('menu.maquila') }}
                    </a>
                    <a href="{{ route('produccion.facturacion') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        {{ __('menu.billing') }}
                    </a>
                    <a href="{{ route('produccion.entrega') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        {{ __('menu.delivery') }}
                    </a>
                    <a href="{{ route('produccion.ordenes_produccion') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                        {{ __('menu.work_orders') }}
                    </a> --}}
                </div>
            </div>
            @endcan

            {{-- Entregas --}}
            @can('asideEntrega')
                <div>
                    <button
                        @click="toggleSection('entregas')"
                        :class="openSections.entregas ? 'underline text-blue-400' : ''"
                        class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                    >
                        <span>{{ __('menu.deliveries') }}</span>
                        <svg :class="openSections.entregas ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                            class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="openSections.entregas" x-transition class="pl-6 mt-1 space-y-1">
                        <a href="{{ route('produccion.tareas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                            {{ __('menu.deliveries_admin') }}
                        </a>

                        @livewire('aside-hojas', ['ubicacion' => 'entregas'])
                    </div>
                </div>
            @endcan


            {{-- Facturación --}}
            @can('asideFacturacion')
                <div>
                    <button
                        @click="toggleSection('facturacion')"
                        :class="openSections.facturacion ? 'underline text-blue-400' : ''"
                        class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                    >
                        <span>{{ __('menu.invoicing') }}</span>
                        <svg :class="openSections.facturacion ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                            class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="openSections.facturacion" x-transition class="pl-6 mt-1 space-y-1">
                        <a href="{{ route('produccion.tareas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                            {{ __('menu.invoicing_admin') }}
                        </a>
                        @livewire('aside-hojas', ['ubicacion' => 'facturacion'])
                    </div>
                </div>
            @endcan

         
            {{-- Configuración --}}
            <div>
                <button
                    @click="toggleSection('configuracion')"
                    :class="openSections.configuracion ? 'underline text-blue-400' : ''"
                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                >
                    <span>{{ __('menu.settings') }}</span>
                    <svg :class="openSections.configuracion ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                         class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <div x-show="openSections.configuracion" x-transition class="pl-6 mt-1 space-y-2">
                       @hasanyrole('admin')
                            {{-- Envío --}}
                            <div>
                                <button
                                    @click="toggleSection('config_envio')"
                                    :class="openSections.config_envio ? 'underline text-blue-400' : ''"
                                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                                >
                                    <span>{{ __('menu.shipping') }}</span>
                                    <svg :class="openSections.config_envio ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                                        class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                <div x-show="openSections.config_envio" x-transition class="pl-6 mt-1 space-y-1">
                                    <a href="{{ route('catalogos.paises.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.countries') }}
                                    </a>
                                    <a href="{{ route('catalogos.estados.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.states') }}
                                    </a>
                                    <a href="{{ route('catalogos.ciudades.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.cities') }}
                                    </a>
                                    <a href="{{ route('catalogos.tipoenvio.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.shipping_types') }}
                                    </a>
                                </div>
                            </div>
                        @endhasanyrole
                    
                            {{-- Productos --}}
                        @hasanyrole('admin')
                            <div>
                                <button
                                    @click="toggleSection('config_productos')"
                                    :class="openSections.config_productos ? 'underline text-blue-400' : ''"
                                    class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                                >
                                    <span>{{ __('menu.products') }}</span>
                                    <svg :class="openSections.config_productos ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                                        class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                <div x-show="openSections.config_productos" x-transition class="pl-6 mt-1 space-y-1">
                                    <a href="{{ route('catalogos.categorias.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.categories') }}
                                    </a>
                                    <a href="{{ route('catalogos.producto.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.products_list') }}
                                    </a>
                                    <a href="{{ route('catalogos.caracteristica.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.features') }}
                                    </a>
                                    <a href="{{ route('catalogos.opciones.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.options') }}
                                    </a>
                                    <a href="{{ route('catalogos.producto.layout') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.layout') }}
                                    </a>
                                    <a href="{{ route('catalogos.tallas.tallas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.sizes') }}
                                    </a>
                                    <a href="{{ route('catalogos.tallas.grupos') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.groups') }}
                                    </a>
                                    <a href="{{ route('catalogos.flujoProduccion') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.flow') }}
                                    </a>
                                    <a href="{{ route('catalogos.hojaFiltrosProduccion') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.sheets') }}
                                    </a>
                                    <a href="{{ route('catalogos.flujoFiltrosProduccion') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                        {{ __('menu.filters') }}
                                    </a>
                                </div>
                            </div>
                        @endhasanyrole
                        
                        @hasanyrole('admin')
                        {{-- Importación --}}
                        <div>
                            <button
                                @click="toggleSection('config_importacion')"
                                :class="openSections.config_importacion ? 'underline text-blue-400' : ''"
                                class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                            >
                                <span> importacion </span>
                                <svg :class="openSections.config_importacion ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                                    class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            <div x-show="openSections.config_importacion" x-transition class="pl-6 mt-1 space-y-1">
                                <a href="{{ route('importacion.proyectos.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                    Importa proyecto legacy
                                </a>

                            </div>
                        </div>
                        @endhasanyrole

                    {{-- Usuarios --}}
                    @can('asideusuariosdesplegable') 
                    <div>
                        <button
                            @click="toggleSection('config_usuarios')"
                            :class="openSections.config_usuarios ? 'underline text-blue-400' : ''"
                            class="w-full flex justify-between items-center px-4 py-2 rounded hover:bg-gray-800 focus:outline-none"
                        >
                            <span>{{ __('menu.users') }}</span>
                            <svg :class="openSections.config_usuarios ? 'rotate-90 transform text-blue-300' : 'text-gray-400'"
                                 class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div x-show="openSections.config_usuarios" x-transition class="pl-6 mt-1 space-y-1">


                            {{-- <a href="{{ route('usuarios.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                {{ __('menu.users_list') }}
                            </a> --}}
                            @can('asideusuarios.administraclientes')
                                <a href="{{ route('usuarios.clientes') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                    {{-- {{ __('menu.permissions') }} --}}
                                    Clientes
                                </a>

                            @endcan

                            @can('asideusuarios.administraproveedor')
                        
                                <a href="{{ route('usuarios.proveedor') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                    {{-- {{ __('menu.permissions') }} --}}
                                    Proveedor
                                </a>
                            
                                
                            @endcan

                            @can('asideusuarios.administrastaff')
                                <a href="{{ route('usuarios.staff') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                    {{-- {{ __('menu.permissions') }} --}}
                                    Staff
                                </a>
                            @endcan


                            @can('asideusuarios.administraadmin')
                                <a href="{{ route('usuarios.admin') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                    {{-- {{ __('menu.permissions') }} --}}
                                    Admin
                                </a>
                                
                            @endcan


                           @hasanyrole('admin')
                            <a href="{{ route('permisos.index') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                {{ __('menu.permissions') }}
                            </a>

                            <a href="{{ route('permisos.empresas') }}" class="block px-2 py-1 rounded hover:bg-gray-800">
                                Administra empresas
                            </a>

                            
                            @endhasanyrole


                        </div>
                    </div>
                    @endcan
                </div>
            </div>
      

            {{-- Perfil --}}
            <div class="mt-6 border-t border-gray-700 pt-4 px-4">
                <div class="text-sm">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-400">{{ Auth::user()->email }}</div>
                <a href="{{ route('profile.edit') }}" class="block mt-2 text-blue-400 hover:underline text-sm">
                    {{ __('menu.edit_profile') }}
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="text-red-400 hover:underline text-sm">
                        {{ __('menu.logout') }}
                    </button>
                </form>
            </div>
            
        </nav>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('alpine:init', () => {
    Alpine.data('asideMenu', () => ({
      // Estado
      sidebarOpen: false,
      sidebarPinned: JSON.parse(localStorage.getItem('sidebarPinned') || 'false'),
      openSections: JSON.parse(localStorage.getItem('openSections') || '{}'),
      selectedRoute: '{{ request()->route()->getName() }}',

      // Inicialización
      init() {
        this.sidebarOpen = this.sidebarPinned || window.innerWidth >= 1024;
        this.$nextTick(() => this.broadcast());
        this.$watch('sidebarOpen', () => this.broadcast());
      },

      // Métodos de UI
      open() { this.sidebarOpen = true; },
      close() {
        this.sidebarOpen = false;
        this.sidebarPinned = false;
        localStorage.setItem('sidebarPinned', 'false');
      },
      togglePin() {
        this.sidebarPinned = !this.sidebarPinned;
        localStorage.setItem('sidebarPinned', JSON.stringify(this.sidebarPinned));
        if (!this.sidebarPinned && window.innerWidth < 1024) this.sidebarOpen = false;
        else if (this.sidebarPinned) this.sidebarOpen = true;
      },
      onOutsideClick() {
        if (window.innerWidth < 1024 && !this.sidebarPinned) this.sidebarOpen = false;
      },
      onResize() {
        if (!this.sidebarPinned) this.sidebarOpen = window.innerWidth >= 1024;
      },

      // Submenús
      toggleSection(name) {
        this.openSections[name] = !this.openSections[name];
        localStorage.setItem('openSections', JSON.stringify(this.openSections));
      },
      isActive(route) {
        return this.selectedRoute === route;
      },

      // Broadcast a otros componentes
      broadcast() {
        window.dispatchEvent(new CustomEvent('aside:state', { detail: { open: this.sidebarOpen } }));
      },
    }));
  });
});
</script>
