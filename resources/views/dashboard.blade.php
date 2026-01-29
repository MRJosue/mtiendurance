<x-app-layout>
    <x-slot name="header" class="pl-64">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">

        
        @hasanyrole('admin|cliente|cliente_principal|cliente_subordinado|jefediseñador')
            
        

                <div class="w-full mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            {{-- <h1 class="text-2xl font-bold mb-4">{{ $hoja->nombre }} ({{ $hoja->slug }})</h1> --}}
                            <hr/>
                            @livewire('dashboard.cliente-panel')
                        </div>

             
                    </div>
                </div>


            {{-- <div class="w-full px-4 sm:px-6 lg:px-20 xl:px-32 mx-auto">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        @livewire('dashboard.cliente-panel')
                    </div>
                </div>
                
            </div> --}}


        @endhasanyrole


        {{-- @hasanyrole('admin|proveedor')
            <div class="w-full px-4 sm:px-6 lg:px-20 xl:px-32 mx-auto">

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        @livewire('dashboard.proveedor-panel')
                    </div>
                </div>
                    
            </div>
        @endhasanyrole --}}


        {{-- @hasanyrole('admin|estaf|cliente_principal|cliente_subordinado')
        <div class="w-full px-4 sm:px-6 lg:px-20 xl:px-32 mx-auto">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100"> --}}
                    {{-- @livewire('dashboard.estaf-panel') --}}
                {{-- </div>
            </div>
        </div>

        @endhasanyrole --}}


        {{-- @hasanyrole('admin|jefediseñador')
        <div class="w-full px-4 sm:px-6 lg:px-20 xl:px-32 mx-auto">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @livewire('dashboard.jefe-disenador-panel')
                </div>
            </div>
        </div>
        @endhasanyrole --}}
      
        @hasanyrole('admin|diseñador')

                

                <div class="w-full mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            {{-- <h1 class="text-2xl font-bold mb-4">{{ $hoja->nombre }} ({{ $hoja->slug }})</h1> --}}
                            <hr/>
                              @livewire('dashboard.disenador-panel')
                        </div>

                     
                    </div>
                </div>


        @endhasanyrole
      

        @hasanyrole('admin|operador')
                <div class="w-full mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            {{-- <h1 class="text-2xl font-bold mb-4">{{ $hoja->nombre }} ({{ $hoja->slug }})</h1> --}}
                            {{-- <hr/> --}}
                                    {{-- @livewire('dashboard.operador-panel') --}}
                        </div>

                     
                    </div>
                </div>

        @endhasanyrole
        

        @hasanyrole('admin|proveedor')

        <div class="w-full mx-auto sm:px-6 lg:px-8">    
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                <div class="p-6 text-gray-900 dark:text-gray-100">
                            <hr/>
                                @livewire('proveedores.disenos-proveedor')
                            </div>
            </div>
        </div>

        
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                <div class="p-6 text-gray-900 dark:text-gray-100">
                            <hr/>
                                @livewire('dashboard.proveedor-panel.pedidos-proveedor-dashboard')
                            </div>
            </div>
        </div>

        @endhasanyrole



                <div class="w-full mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            {{-- <h1 class="text-2xl font-bold mb-4">{{ $hoja->nombre }} ({{ $hoja->slug }})</h1> --}}
                            <hr/>
                              <livewire:dashboard.notificaciones.notificaciones-lista />
                        </div>

                     
                    </div>
                </div>



        
      

        {{-- <div class="w-full px-4 sm:px-6 lg:px-20 xl:px-32 mx-auto">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
            
                    @livewire('client-message')
                </div>
            </div>
        </div> --}}

            @role('admin')
            {{-- <div class="p-2 sm:p-3">
                <div class="overflow-x-auto bg-white rounded-lg shadow">

                    <livewire:modales.proyecto-info-modal/>
                    <livewire:pedidos-power-grid />
                    
                </div>
            </div> --}}


            @endrole
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
