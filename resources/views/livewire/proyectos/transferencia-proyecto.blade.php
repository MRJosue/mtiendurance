<div>
        @can('proyectos.transferencia.generar')
            <div class="p-4 border rounded-lg shadow bg-gray-50 dark:bg-gray-700 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                        Transferencia de Propietario
                    </h3>

                    @if(!$transferencia)
                        <button
                            type="button"
                            wire:click="abrirModalSolicitud"
                            class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-sm"
                        >
                            Generar solicitud
                        </button>
                    @endif
                </div>
            

                {{-- RESUMEN --}}
                @if($transferencia)
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4 space-y-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm text-gray-700 dark:text-gray-200">
                                <span class="font-semibold">Estado:</span>
                                <span class="inline-flex px-2 py-1 rounded text-xs
                                    @if($transferencia->estado === 'PENDIENTE') bg-yellow-100 text-yellow-800
                                    @elseif($transferencia->estado === 'APROBADO') bg-green-100 text-green-800
                                    @elseif($transferencia->estado === 'APLICADO') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-700
                                    @endif
                                ">
                                    {{ $transferencia->estado }}
                                </span>
                            </p>

                            <p class="text-xs text-gray-500 dark:text-gray-300">
                                ID: {{ $transferencia->id }}
                            </p>
                        </div>

                        <p class="text-sm text-gray-700 dark:text-gray-200">
                            <span class="font-semibold">Propietario actual:</span>
                            {{ $transferencia->ownerActual->name ?? '—' }}
                        </p>

                        <p class="text-sm text-gray-700 dark:text-gray-200">
                            <span class="font-semibold">Nuevo propietario:</span>
                            {{ $transferencia->ownerNuevo->name ?? '—' }}
                        </p>

                        @if($transferencia->motivo)
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                <span class="font-semibold">Motivo:</span>
                                {{ $transferencia->motivo }}
                            </p>
                        @endif

                        {{-- ACCIONES --}}
                        <div class="pt-2 flex flex-wrap gap-2">
                            @can('proyectos.transferencia.aprobar')
                                @if($transferencia->estado === 'PENDIENTE')
                                    <button
                                        type="button"
                                        wire:click="autorizar"
                                        class="px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm"
                                    >
                                        Autorizar
                                    </button>
                                @endif
                            @endcan

                            @if($transferencia->estado === 'PENDIENTE')
                                <button
                                    type="button"
                                    wire:click="cancelar"
                                    class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm"
                                >
                                    Cancelar
                                </button>
                            @endif

                            @can('proyectos.transferencia.aplicar')
                                @if($transferencia->estado === 'PENDIENTE')
                                    <button
                                        type="button"
                                        wire:click="aplicarTransferencia"
                                        class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
                                    >
                                        Aplicar transferencia
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        No hay solicitudes activas para este proyecto.
                    </p>
                @endif
            </div>
        @endcan

        {{-- ADMIN: DIRECTO CON MODAL --}}
        @role('admin')
          <div class="p-4 border rounded-lg shadow bg-gray-50 dark:bg-gray-700 space-y-3">
            <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="text-sm text-gray-700 dark:text-gray-200">
                        <span class="font-semibold">Admin:</span> puedes forzar el cambio sin solicitud.
                    </div>

                    <button
                        type="button"
                        wire:click="abrirModalAdminDirecto"
                        class="px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm"
                    >
                        Transferencia directa (Admin)
                    </button>
                </div>
            </div>
          </div>
        @endrole

        @role('admin')
            <div class="p-4 border rounded-lg shadow bg-gray-50 dark:bg-gray-700 space-y-3">
                <div class="border-t border-gray-200 dark:border-gray-600 pt-3 space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="text-sm text-gray-700 dark:text-gray-200">
                            <span class="font-semibold">Admin:</span> puedes iniciar reprogramación del proyecto.
                        </div>

                        <button
                            type="button"
                            wire:click="abrirModalAdminReprogramar"
                            class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm"
                        >
                            Reprogramar (Admin)
                        </button>
                    </div>
                </div>
            </div>
        @endrole

    </div>

    {{-- MODAL: SOLICITUD --}}
    @if($modalSolicitud)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-xl p-6 space-y-4 shadow-lg">
                <div class="flex items-start justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Nueva solicitud de transferencia
                    </h2>
                    <button
                        type="button"
                        wire:click="$set('modalSolicitud', false)"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white"
                    >
                        ✕
                    </button>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Nuevo propietario</label>
                    <select wire:model.defer="owner_nuevo_id"
                            class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Seleccione un usuario subordinado</option>

                        @forelse($subordinadosUsuarios as $usuario)
                            <option value="{{ $usuario->id }}">
                                {{ $usuario->name }} — {{ $usuario->email }} (ID: {{ $usuario->id }})
                            </option>
                        @empty
                            <option value="" disabled>No hay subordinados disponibles</option>
                        @endforelse
                    </select>
                    @error('owner_nuevo_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Motivo (opcional)</label>
                    <textarea wire:model.defer="motivo" class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:text-gray-100" rows="3"></textarea>
                    @error('motivo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <button
                        type="button"
                        wire:click="$set('modalSolicitud', false)"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-600 dark:text-white rounded hover:bg-gray-300 dark:hover:bg-gray-500"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="crearSolicitud"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                    >
                        Crear solicitud
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL: ADMIN DIRECTO --}}
    @if($modalAdminDirecto)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-xl p-6 space-y-4 shadow-lg">
                <div class="flex items-start justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Transferencia directa (Admin)
                    </h2>
                    <button
                        type="button"
                        wire:click="$set('modalAdminDirecto', false)"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white"
                    >
                        ✕
                    </button>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 rounded p-3 text-sm text-yellow-900 dark:text-yellow-200">
                    Esta acción cambia el propietario del proyecto <span class="font-semibold">de inmediato</span>.
                    Se registrará en historial como <span class="font-semibold">APLICADO</span>.
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Nuevo propietario</label>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                Buscar usuario (nombre o email)
                            </label>

                            <input
                                type="text"
                                wire:model.live.debounce.300ms="adminQuery"
                                placeholder="Ej: juan@correo.com o Juan Pérez"
                                class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:text-gray-100"
                            />

                            @if(!empty($adminResultados))
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                                    <ul class="divide-y divide-gray-200 dark:divide-gray-600 max-h-56 overflow-y-auto">
                                        @foreach($adminResultados as $u)
                                            <li class="p-3 hover:bg-gray-50 dark:hover:bg-gray-900/40">
                                                <button
                                                    type="button"
                                                    wire:click="seleccionarAdminUsuario({{ $u['id'] }})"
                                                    class="w-full text-left"
                                                >
                                                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                        {{ $u['name'] }}
                                                    </div>
                                                    <div class="text-xs text-gray-600 dark:text-gray-300">
                                                        {{ $u['email'] }} — ID: {{ $u['id'] }}
                                                    </div>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @elseif(strlen(trim($adminQuery)) >= 2)
                                <p class="text-xs text-gray-500 dark:text-gray-300">Sin resultados…</p>
                            @endif

                            @if($admin_owner_nuevo_id)
                                <p class="text-sm text-gray-700 dark:text-gray-200">
                                    <span class="font-semibold">Seleccionado:</span> ID {{ $admin_owner_nuevo_id }}
                                </p>
                            @endif

                            @error('admin_owner_nuevo_id')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                    @error('admin_owner_nuevo_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Motivo (opcional)</label>
                    <textarea wire:model.defer="admin_motivo" class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:text-gray-100" rows="3"></textarea>
                    @error('admin_motivo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <button
                        type="button"
                        wire:click="$set('modalAdminDirecto', false)"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-600 dark:text-white rounded hover:bg-gray-300 dark:hover:bg-gray-500"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="aplicarTransferenciaDirecta"
                        class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700"
                    >
                        Aplicar ahora
                    </button>
                </div>
            </div>
        </div>
    @endif
    
    @if($modalAdminReprogramar)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-xl p-6 space-y-4 shadow-lg">
                <div class="flex items-start justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Iniciar reprogramación (Admin)
                    </h2>
                    <button
                        type="button"
                        wire:click="$set('modalAdminReprogramar', false)"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white"
                    >
                        ✕
                    </button>
                </div>

                <div class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded p-3 text-sm text-indigo-900 dark:text-indigo-200">
                    Esto activará los flags de reconfiguración y te llevará a la pantalla de reprogramación del proyecto.
                </div>

                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <button
                        type="button"
                        wire:click="$set('modalAdminReprogramar', false)"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-600 dark:text-white rounded hover:bg-gray-300 dark:hover:bg-gray-500"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="adminGenerarReprogramacion"
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
                    >
                        Continuar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
