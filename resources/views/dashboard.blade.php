<x-app-layout>
    <x-slot name="header" class="pl-64">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">

        
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                            {{-- <h1 class="text-2xl font-bold mb-4">{{ $hoja->nombre }} ({{ $hoja->slug }})</h1> --}}
                    <hr/>
                        @can('dashboardclientepreproyectos')
                            @livewire('preproyectos.manage-preprojects')
                        @endcan

                        @canany(['dashboardclienteproyectos', 'dashboardjefediseñadorproyectos'])
                            @livewire('proyectos.manage-projects')
                        @endcanany

                        @can('dashboardclientepedidos')
                            @livewire('dashboard.cliente-panel.pedidos')
                        @endcan
                        </div>
            </div>
        </div>


      
        {{-- @hasanyrole('admin|diseñador') --}}
        @can('dashboardtareasdisenio')
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        {-- <h1 class="text-2xl font-bold mb-4">{{ $hoja->nombre }} ({{ $hoja->slug }})</h1> --}}
                        <hr/>
                        @livewire('dashboard.disenio-panel.tareasdisenio')
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
