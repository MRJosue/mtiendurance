<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <a href="{{route('usuarios.index')}}">{{ __('Usuarios') }}</a>   /
               {{ __('Detalles') }}
           </h2>

           
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @role('admin')
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
            @endrole

            
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div> --}}
        </div>
    </div>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800   shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- <h1 class="text-2xl font-bold mb-4">Detalles del Usuario</h1> --}}

                    {{-- @livewire('users-table') --}}

                    {{-- @livewire('usuarios.tabla-usuarios') --}}
                 
                    {{-- <h2 class="text-m font-bold mb-4">name:  {{$user->name}}</h2>
                        <br>
                    <h2 class="text-m font-bold mb-4">email: {{$user->email}}</h2>
                     --}}
                    {{-- <div>
                        @livewire('usuarios.cliente-management', ['userId' => $user->id])
                    </div> --}}

                    @can('usuarios.configuracion.seccion.organisacion')
                        <div>
                            @livewire('usuarios.configuraciones-usuario-empresa',  ['userId' => $user->id])
                        </div>
                    @endcan

                    @can('usuarios.configuracion.seccion.sucursales')
                        <div>
                            @livewire('usuarios.configuraciones-usuario-sucursal', ['userId' => $user->id])
                        </div>
                    @endcan

                    @can('usuarios.configuracion.seccion.makeuser')
                        <div>
                            @livewire('usuarios.configuraciones-usuario-makeuser', ['userId' => $user->id])
                        </div>
                    @endcan


                </div>

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div >

                        @role('admin')
                           @livewire('configuraciones-usuario', ['userId' => $user->id])
                        @endrole
        
                    </div>

                </div>
            </div>
        </div>
    </div>


    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800   shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-4">Direccion fiscal</h1>

                    {{-- @livewire('users-table') --}}

                    {{-- @livewire('usuarios.tabla-usuarios') --}}

                     @livewire('usuarios.direcciones-fiscales-crud',['userId'=>$user->id])
                    

                </div>
            </div>
        </div>
    </div>


    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800   shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-4">Direccion de entrega</h1>

                    {{-- @livewire('users-table') --}}
                    @livewire('usuarios.direcciones-entrega-crud',['userId'=>$user->id])

                </div>
            </div>
        </div>
    </div>

    @role('admin')
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <livewire:usuarios.activar-inactivar-usuario :user="$user" />
                    </div>
                </div>
            </div>
        </div>
    @endrole

    @role('admin')
        <div class="py-2">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800   shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h1 class="text-2xl font-bold mb-4">Permisos y roles del usuario</h1>
                            {{-- @livewire('usuarios.user-roles-permissions', ['userId' => $user->id]) --}}
                            <livewire:usuarios.ver-actualizar-password-usuario :user="$user" />
                        </div>
                    </div>
                </div>
        </div>
    @endrole

</x-app-layout>
