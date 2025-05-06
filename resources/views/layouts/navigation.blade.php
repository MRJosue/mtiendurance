<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('preproyectos.index')" :active="request()->routeIs('preproyectos.index')">
                        {{ __('Pre proyectos') }}
                    </x-nav-link>



                    <x-nav-link :href="route('proyectos.index')" :active="request()->routeIs('proyectos.index')">
                        {{ __('proyectos') }}
                    </x-nav-link>


                    {{-- Diseño --}}
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">

                            <x-slot name="trigger">
                            
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Diseño</div>

                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>

                                </button>
                        
                            </x-slot>


                            <x-slot name="content">

                           
                                <x-dropdown-link :href="route('disenio.admin_tarea')">
                                    {{ __('Tareas Diseño') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('disenio.index')">
                                    {{ __('Diseño') }}
                                </x-dropdown-link>


                   


                            </x-slot>
                        </x-dropdown>
                    </div>
                    {{-- produccion --}}
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">

                            <x-slot name="trigger">
                               
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Producción</div>

                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>

                                </button>
                        
                            </x-slot>


                            <x-slot name="content">

                              
                                <x-dropdown-link :href="route('produccion.tareas')">
                                    {{ __('Tareas Produccion') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('programacion.index')">
                                    {{ __('Programacion') }}
                                </x-dropdown-link>


                                <x-dropdown-link :href="route('produccion.ordenes_produccion')">
                                    {{ __('Ordenes de Produccion') }}
                                </x-dropdown-link>





                            </x-slot>
                        </x-dropdown>
                    </div>

                    {{-- catalogos --}}
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">

                            <x-slot name="trigger">
                                @hasanyrole('admin')
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Envio</div>

                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>

                                </button>
                                @endhasanyrole
                            </x-slot>


                            <x-slot name="content">

                                @hasanyrole('admin')

                                <x-dropdown-link :href="route('catalogos.paises.index')">
                                    {{ __('Paises') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('catalogos.estados.index')">
                                    {{ __('Estados') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('catalogos.ciudades.index')">
                                    {{ __('Ciudades') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('catalogos.tipoenvio.index')">
                                    {{ __('Tipos de envio') }}
                                </x-dropdown-link>

                                @endhasanyrole


                            </x-slot>
                        </x-dropdown>
                    </div>

                    {{-- configuracion de producto --}}
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">

                            <x-slot name="trigger">
                                @hasanyrole('admin')
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Productos</div>

                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>

                                </button>
                                @endhasanyrole
                            </x-slot>


                            <x-slot name="content">

                                @hasanyrole('admin')

                                <x-dropdown-link :href="route('catalogos.categorias.index')">
                                    {{ __('Categorias') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('catalogos.producto.index')">
                                    {{ __('Producto') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('catalogos.caracteristica.index')">
                                    {{ __('Caracteristicas') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('catalogos.opciones.index')">
                                    {{ __('Opciones de Caracteristicas') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('catalogos.producto.layout')">
                                    {{ __('Layout producto') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('catalogos.tallas.tallas')">
                                    {{ __('Tallas') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('catalogos.tallas.grupos')">
                                    {{ __('Grupos') }}
                                </x-dropdown-link>


                                @endhasanyrole


                            </x-slot>
                        </x-dropdown>
                    </div>


                    {{-- usuarios --}}
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">

                              
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Usuarios</div>

                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>

                           
                            </x-slot>

                            <x-slot name="content">
                            
                                    <x-dropdown-link :href="route('usuarios.index')">
                                        {{ __('Usuarios') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('permisos.index')">
                                        {{ __('Permisos') }}
                                    </x-dropdown-link>

                        
                            </x-slot>
                        </x-dropdown>
                    </div>

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">

                @livewire('notificaciones.notificaciones')

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

        <!-- Responsive Navigation Menu -->
        <!-- Responsive Navigation Menu -->
        <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>


                <x-responsive-nav-link  :href="route('preproyectos.index')" :active="request()->routeIs('preproyectos.index')">
                    {{ __('Pre proyectos') }}
                </x-responsive-nav-link >

                <x-responsive-nav-link :href="route('proyectos.index')" :active="request()->routeIs('proyectos.index')">
                    {{ __('Proyectos') }}
                </x-responsive-nav-link>



                <x-nav-link :href="route('disenio.index')" :active="request()->routeIs('disenio.index')">
                    {{ __('Diseño') }}
                </x-nav-link>

                <x-nav-link :href="route('disenio.admin_tarea')" :active="request()->routeIs('disenio.admin_tarea')">
                    {{ __('Tareas') }}
                </x-nav-link>


            </div>

            <!-- Responsive Catálogos -->
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="space-y-1">

                    @hasanyrole('admin')


                    <x-responsive-nav-link :href="route('catalogos.categorias.index')">
                        {{ __('Categorias') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('catalogos.producto.index')">
                        {{ __('Producto') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('catalogos.caracteristica.index')">
                        {{ __('Caracteristicas') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('catalogos.opciones.index')">
                        {{ __('Opciones de Caracteristicas') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('catalogos.paises.index')">
                        {{ __('Paises') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('catalogos.estados.index')">
                        {{ __('Estados') }}
                    </x-responsive-nav-link>
                    <x-dropdown-link :href="route('catalogos.ciudades.index')">
                        {{ __('Ciudades') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('catalogos.tipoenvio.index')">
                        {{ __('Tipos de envio') }}
                    </x-responsive-nav-link>

                    @endhasanyrole


                </div>
            </div>

            <!-- Responsive Usuarios -->
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="space-y-1">
                    @hasanyrole('admin')
                    <x-responsive-nav-link :href="route('usuarios.index')">
                        {{ __('Usuarios') }}
                    </x-responsive-nav-link>

                    {{-- <x-responsive-nav-link :href="route('permisos.index')">
                        {{ __('Permisos') }}
                    </x-responsive-nav-link> --}}
                    @endhasanyrole
                </div>
            </div>

            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>



        <!--     Second Navigation Menu -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('dashboard') }}">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                        </a>
                    </div>
    
                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
    
                        <x-nav-link :href="route('preproyectos.index')" :active="request()->routeIs('preproyectos.index')">
                            {{ __('Pre proyectos') }}
                        </x-nav-link>
    
    
    
                        <x-nav-link :href="route('proyectos.index')" :active="request()->routeIs('proyectos.index')">
                            {{ __('proyectos') }}
                        </x-nav-link>
    
    
                        {{-- Diseño --}}
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <x-dropdown align="right" width="48">
    
                                <x-slot name="trigger">
                                    @hasanyrole('admin')
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        <div>Diseño</div>
    
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
    
                                    </button>
                                    @endhasanyrole
                                </x-slot>
    
    
                                <x-slot name="content">
    
                                    @hasanyrole('admin')
                                    <x-dropdown-link :href="route('disenio.admin_tarea')">
                                        {{ __('Tareas Diseño') }}
                                    </x-dropdown-link>
    
                                    <x-dropdown-link :href="route('disenio.index')">
                                        {{ __('Diseño') }}
                                    </x-dropdown-link>
    
    
                                    @endhasanyrole
    
    
                                </x-slot>
                            </x-dropdown>
                        </div>
                        {{-- produccion --}}
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <x-dropdown align="right" width="48">
    
                                <x-slot name="trigger">
                                    @hasanyrole('admin')
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        <div>Producción</div>
    
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
    
                                    </button>
                                    @endhasanyrole
                                </x-slot>
    
    
                                <x-slot name="content">
    
                                    @hasanyrole('admin')
                                    <x-dropdown-link :href="route('produccion.tareas')">
                                        {{ __('Tareas Produccion') }}
                                    </x-dropdown-link>
    
                                    <x-dropdown-link :href="route('programacion.index')">
                                        {{ __('Programacion') }}
                                    </x-dropdown-link>
    
    
                                    <x-dropdown-link :href="route('produccion.ordenes_produccion')">
                                        {{ __('Ordenes de Produccion') }}
                                    </x-dropdown-link>
    
    
    
                                    @endhasanyrole
    
    
                                </x-slot>
                            </x-dropdown>
                        </div>
    
                        {{-- catalogos --}}
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <x-dropdown align="right" width="48">
    
                                <x-slot name="trigger">
                                    @hasanyrole('admin')
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        <div>Envio</div>
    
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
    
                                    </button>
                                    @endhasanyrole
                                </x-slot>
    
    
                                <x-slot name="content">
    
                                    @hasanyrole('admin')
    
                                    <x-dropdown-link :href="route('catalogos.paises.index')">
                                        {{ __('Paises') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('catalogos.estados.index')">
                                        {{ __('Estados') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('catalogos.ciudades.index')">
                                        {{ __('Ciudades') }}
                                    </x-dropdown-link>
    
                                    <x-dropdown-link :href="route('catalogos.tipoenvio.index')">
                                        {{ __('Tipos de envio') }}
                                    </x-dropdown-link>
    
                                    @endhasanyrole
    
    
                                </x-slot>
                            </x-dropdown>
                        </div>
    
                        {{-- configuracion de producto --}}
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <x-dropdown align="right" width="48">
    
                                <x-slot name="trigger">
                                    @hasanyrole('admin')
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        <div>Productos</div>
    
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
    
                                    </button>
                                    @endhasanyrole
                                </x-slot>
    
    
                                <x-slot name="content">
    
                                    @hasanyrole('admin')
    
                                    <x-dropdown-link :href="route('catalogos.categorias.index')">
                                        {{ __('Categorias') }}
                                    </x-dropdown-link>
    
                                    <x-dropdown-link :href="route('catalogos.producto.index')">
                                        {{ __('Producto') }}
                                    </x-dropdown-link>
    
                                    <x-dropdown-link :href="route('catalogos.caracteristica.index')">
                                        {{ __('Caracteristicas') }}
                                    </x-dropdown-link>
    
                                    <x-dropdown-link :href="route('catalogos.opciones.index')">
                                        {{ __('Opciones de Caracteristicas') }}
                                    </x-dropdown-link>
    
                                    <x-dropdown-link :href="route('catalogos.producto.layout')">
                                        {{ __('Layout producto') }}
                                    </x-dropdown-link>
    
                                    <x-dropdown-link :href="route('catalogos.tallas.tallas')">
                                        {{ __('Tallas') }}
                                    </x-dropdown-link>
    
                                    <x-dropdown-link :href="route('catalogos.tallas.grupos')">
                                        {{ __('Grupos') }}
                                    </x-dropdown-link>
    
    
                                    @endhasanyrole
    
    
                                </x-slot>
                            </x-dropdown>
                        </div>
    
    
                        {{-- usuarios --}}
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
    
                                    @hasanyrole('admin')
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        <div>Usuarios</div>
    
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
    
                                    @endhasanyrole
                                </x-slot>
    
                                <x-slot name="content">
                                    @hasanyrole('admin')
                                        <x-dropdown-link :href="route('usuarios.index')">
                                            {{ __('Usuarios') }}
                                        </x-dropdown-link>
    
                                        <x-dropdown-link :href="route('permisos.index')">
                                            {{ __('Permisos') }}
                                        </x-dropdown-link>
    
                                    @endhasanyrole
                                </x-slot>
                            </x-dropdown>
                        </div>
    
                    </div>
                </div>
    
                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
    
                    @livewire('notificaciones.notificaciones')
    
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
    
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
    
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
    
                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
    
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
    
                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
</nav>
<script>
    function toggleDropdown() {
        const dropdownMenu = document.getElementById('dropdownMenu');
        dropdownMenu.classList.toggle('hidden');
    }
</script>
