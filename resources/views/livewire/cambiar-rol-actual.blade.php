<div
    x-data="{ abierto: false }"
    class="fixed bottom-9 left-4 z-50 w-80 max-w-full bg-white border border-gray-300 rounded-xl shadow-lg overflow-hidden"
>

    <!-- Cabecera minimizable -->
    <div class="bg-blue-600 text-white px-4 py-2 cursor-pointer flex justify-between items-center" @click="abierto = !abierto">
        <span class="font-semibold">Cambiar Rol</span>
        <svg :class="{ 'rotate-180': abierto }" class="h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </div>


    <!-- Contenido -->
    <div x-show="abierto" x-transition class="p-4">
        @if (session()->has('message'))
            <div class="bg-green-100 text-green-800 p-2 rounded mb-4 text-sm">
                {{ session('message') }}
            </div>
        @endif

        <div class="mb-3">
        @livewire('user-roles-permissions')
         </div>

        <div class="mb-3">
       
            <livewire:switch-user />
        </div>

        <div class="mb-3">
            <label for="rolActual" class="block text-sm font-medium text-gray-700">Rol actual</label>
            <select wire:model="rolActual" id="rolActual" class="mt-1 w-full rounded border-gray-300">
                <option value="">-- Selecciona un rol --</option>
                @foreach ($rolesDisponibles as $rol)
                    <option value="{{ $rol }}">{{ $rol }}</option>
                @endforeach
            </select>
        </div>



                            <!-- Botón para abrir modal de permisos -->
        <div class="mb-3">
            <button wire:click="$set('modalAsignarPermisos', true)" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Permisos
            </button>
        </div>

        <!-- Botón para abrir modal de crear grupo orden -->
        <div class="mb-3">
            <button wire:click="$set('modalCrearGrupoOrden', true)" class="text-sm text-purple-600 hover:underline flex items-center gap-1">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Grupo de Permisos
            </button>
        </div>

        <button
            wire:click="actualizarRol"
            class="w-full px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
        >
            Cambiar Rol
        </button>


    </div>

    @if($modalCrearGrupoOrden)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">Crear nuevo grupo de permisos</h2>

                @livewire('crear-grupo-orden', key('crear-grupo-orden'))

                <div class="flex justify-end mt-4">
                    <button wire:click="$set('modalCrearGrupoOrden', false)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cerrar</button>
                </div>
            </div>
        </div>
    @endif

    @if($modalAsignarPermisos)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-7xl h-[90vh] overflow-y-auto">
                <h2 class="text-2xl font-bold mb-6">Permisos del Rol: {{ $rolActual }}</h2>

                @livewire('permisos-por-rol', ['rol' => $rolActual], key('permisos-' . $rolActual))

                <div class="flex justify-end mt-6 sticky bottom-0 bg-white py-4">
                    <button wire:click="$set('modalAsignarPermisos', false)"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                        Cerrar
                    </button>

                    <button wire:click="$set('modalCrearPermiso', true)"
                        class="ml-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nuevo permiso
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($modalCrearPermiso)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">Crear nuevo permiso</h2>

                @livewire('crear-permiso', ['rol' => $rolActual], key('crear-' . $rolActual))

                <div class="flex justify-end mt-4">
                    <button wire:click="$set('modalCrearPermiso', false)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cerrar</button>
                </div>
            </div>
        </div>
    @endif


</div>
