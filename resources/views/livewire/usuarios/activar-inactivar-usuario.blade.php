<div
    x-data="{
        showDeactivate: @entangle('showDeactivateModal'),
        showActivate: @entangle('showActivateModal')
    }"
    class="container mx-auto p-6"
>
    <!-- Botones de acción -->
    <div class="mb-4 flex flex-wrap gap-3">
        @if($user->ind_activo)
            <button
                type="button"
                class="w-full sm:w-auto px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
                wire:click="openDeactivateModal"
            >
                Inactivar Usuario
            </button>
        @endif

        @if(!$user->ind_activo)
            <button
                type="button"
                class="w-full sm:w-auto px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed"
                wire:click="openActivateModal"
            >
                Activar Usuario
            </button>
        @endif
    </div>

    {{-- Info rápida del usuario --}}
    <div class="bg-white rounded-lg shadow p-4 text-sm text-gray-700">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <p class="font-semibold">
                    Usuario #{{ $user->id }} — {{ $user->name }}
                </p>
                <p class="text-gray-600">
                    Tipo: <span class="font-medium">{{ $user->tipo_texto ?? $user->tipo }}</span>
                    @if($user->es_propietario)
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-700">
                            Propietario
                        </span>
                    @endif
                </p>
            </div>
            <div>
                @if($user->ind_activo)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                        Activo
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                        Inactivo
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal INACTIVAR --}}
    <div
        x-cloak
        x-show="showDeactivate"
        x-transition
        class="fixed inset-0 z-40 flex items-center justify-center bg-black/50"
    >
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6 relative">
            <h2 class="text-lg sm:text-xl font-bold mb-4 text-red-600">
                Confirmar inactivación de usuario
            </h2>

            <p class="text-sm text-gray-700 mb-4">
                Estás a punto de inactivar al usuario
                <span class="font-semibold">{{ $user->name }}</span>.
            </p>

            @if($deactivateStats['es_propietario'])
                <div class="mb-4 text-sm text-gray-700 space-y-1">
                    <p class="font-semibold">
                        Este usuario es propietario. Se aplicará lo siguiente:
                    </p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Subordinados afectados: <span class="font-semibold">{{ $deactivateStats['total_subordinados'] }}</span></li>
                        <li>Total de usuarios que serán inactivados (incluyendo propietario):
                            <span class="font-semibold">{{ $deactivateStats['total_usuarios_afectados'] }}</span>
                        </li>
                    </ul>
                </div>
            @else
                <div class="mb-4 text-sm text-gray-700 space-y-1">
                    <p class="font-semibold">
                        Este usuario es subordinado (no propietario). Se aplicará lo siguiente:
                    </p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Se inactivará solo este usuario.</li>
                    </ul>
                </div>
            @endif

            <p class="text-xs text-red-500 mb-4">
                Esta acción no elimina registros, pero puede afectar el acceso del usuario.
            </p>

            <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2">
                <button
                    type="button"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                    @click="showDeactivate = false"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700"
                    wire:click="inactivarUsuario"
                >
                    Sí, inactivar
                </button>
            </div>
        </div>
    </div>

    {{-- Modal ACTIVAR --}}
    <div
        x-cloak
        x-show="showActivate"
        x-transition
        class="fixed inset-0 z-40 flex items-center justify-center bg-black/50"
    >
        <div class="bg-white rounded-lg shadow-xl max-w-xl w-full mx-4 p-6 relative">
            <h2 class="text-lg sm:text-xl font-bold mb-4 text-emerald-700">
                Confirmar activación de usuario
            </h2>

            <p class="text-sm text-gray-700 mb-4">
                Vas a activar la cuenta de
                <span class="font-semibold">{{ $user->name }}</span>.
            </p>

            <div class="mb-4 text-sm text-gray-700 space-y-1">
                <p class="font-semibold">Se realizará lo siguiente:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Se activará únicamente este usuario.</li>
                </ul>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2">
                <button
                    type="button"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                    @click="showActivate = false"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                    wire:click="activarUsuario"
                >
                    Sí, activar
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('usuario-actualizado', (event) => {
                console.log('Usuario actualizado desde Livewire', event.detail);
            });
        });
    </script>
</div>
