<x-app-layout>
    <x-slot name="header" class="pl-64">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="dashboard-shell">

        
        @can('dashboardclientepreproyectos')
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="dashboard-panel">
                <div class="dashboard-panel__body">
                           
                    <hr class="dashboard-divider"/>
                    @livewire('preproyectos.manage-preprojects')

                </div>
            </div>
        </div>
        @endcan

        @canany('dashboardclienteproyectos')
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="dashboard-panel">
                <div class="dashboard-panel__body">       
                    <hr class="dashboard-divider"/>
                    @livewire('proyectos.manage-projects')
                </div>
            </div>
        </div>
       
        @endcanany

        @can('dashboardclientepedidos')
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="dashboard-panel">
                <div class="dashboard-panel__body">       
                    <hr class="dashboard-divider"/>
                    @livewire('dashboard.cliente-panel.pedidos')
                </div>
            </div>
        </div>
            
        @endcan

        @can('dashboardmuestras')
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="dashboard-panel">
                <div class="dashboard-panel__body">       
                    <hr class="dashboard-divider"/>
                    @livewire('dashboard.cliente-panel.muestras')
                </div>
            </div>
        </div>
            
        @endcan

        {{-- @hasanyrole('admin|diseñador') --}}
        @can('dashboardtareasdisenio')
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="dashboard-panel">
                    <div class="dashboard-panel__body">
                         
                        <hr class="dashboard-divider"/>
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
                <div class="dashboard-panel">
                     
                    <div class="dashboard-panel__body">
                                <hr class="dashboard-divider"/>
                                @livewire('proveedores.disenos-proveedor')
                    </div>
                </div>
            </div> 
        @endcan


        @can('dashboardpedidosproveedor')
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="dashboard-panel">   
                    <div class="dashboard-panel__body">
                    <hr class="dashboard-divider"/>
                        @livewire('dashboard.proveedor-panel.pedidos-proveedor-dashboard')
                    </div>
                </div>
            </div>
        @endcan           


        {{-- @endhasanyrole --}}


        @can('dashboardnotificacion')        
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="dashboard-panel">
                    <div class="dashboard-panel__body">
                        {{-- <h1 class="text-2xl font-bold mb-4">{{ $hoja->nombre }} ({{ $hoja->slug }})</h1> --}}
                        <hr class="dashboard-divider"/>
                            <livewire:dashboard.notificaciones.notificaciones-lista />

                            <livewire:notificaciones.enviar-notificacion />
                            
                    </div>                     
                </div>
            </div>
        @endcan


        </div>
    </div>



    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const url = new URL(window.location.href);
            const reloadFlag = url.searchParams.get('full_reload');
            const reloadKey = 'dashboard_full_reload_done';

            if (reloadFlag === '1') {
                if (!sessionStorage.getItem(reloadKey)) {
                    sessionStorage.setItem(reloadKey, '1');
                    window.location.reload();
                    return;
                }

                sessionStorage.removeItem(reloadKey);
                url.searchParams.delete('full_reload');
                const cleanUrl = `${url.pathname}${url.search ? `?${url.searchParams.toString()}` : ''}${url.hash}`;
                window.history.replaceState({}, document.title, cleanUrl);
            }

            Echo.private('test-channel')
            .listen('SomeEvent', (e) => {
                console.log('Received:', e);
            });

        });

    </script>
    @endpush
</x-app-layout>
