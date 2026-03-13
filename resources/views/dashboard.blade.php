<x-app-layout>
    <x-slot name="header" class="pl-64">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">

        
        @can('dashboardclientepreproyectos')
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                           
                    <hr/>
                    @livewire('preproyectos.manage-preprojects')

                </div>
            </div>
        </div>
        @endcan

        @canany('dashboardclienteproyectos')
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">       
                    <hr/>
                    @livewire('proyectos.manage-projects')
                </div>
            </div>
        </div>
       
        @endcanany

        @can('dashboardclientepedidos')
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">       
                    <hr/>
                    @livewire('dashboard.cliente-panel.pedidos')
                </div>
            </div>
        </div>
            
        @endcan

        @can('dashboardmuestras')
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">       
                    <hr/>
                    @livewire('dashboard.cliente-panel.muestras')
                </div>
            </div>
        </div>
            
        @endcan



      
        {{-- @hasanyrole('admin|diseñador') --}}
        @can('dashboardtareasdisenio')
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <hr/>
                        {{-- @livewire('dashboard.disenio-panel.tareasdisenio') --}}
                        @livewire('tareas.administra-tareas')

                            {{-- @livewire('dashboard.disenador-panel') --}}
                    </div>
                </div>
            </div>
        @endcan

        {{-- @endhasanyrole --}}
      

        {{-- @hasanyrole('admin|operador')
        @endhasanyrole --}}
        
        {{-- @hasanyrole('admin|proveedor') --}}
        @can('dashboarddiseniosproveedor')
            <div class="w-full mx-auto sm:px-6 lg:px-8">    
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                                <hr/>
                                    @livewire('proveedores.disenos-proveedor')
                    </div>
                </div>
            </div> 
        @endcan


        @can('dashboardpedidosproveedor')
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">   
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                    <hr/>
                        @livewire('dashboard.proveedor-panel.pedidos-proveedor-dashboard')
                    </div>
                </div>
            </div>

        @endcan           


        {{-- @endhasanyrole --}}


        @can('dashboardnotificacion')        
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        {{-- <h1 class="text-2xl font-bold mb-4">{{ $hoja->nombre }} ({{ $hoja->slug }})</h1> --}}
                        <hr/>
                            <livewire:dashboard.notificaciones.notificaciones-lista />
                    </div>                     
                </div>
            </div>
        @endcan



    </div>



    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Echo.private('test-channel')
            .listen('SomeEvent', (e) => {
                console.log('Recibido:', e);
            });

        });

    </script>
    @endpush
</x-app-layout>
