<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('configuracionesusuariosucursal') ?? 'true'),
        showDeactivate: @entangle('showDeactivateModal'),
        showActivate: @entangle('showActivateModal'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('configuracionesusuariosucursal', JSON.stringify(this.abierto));
        }
    }"
    class="container mx-auto p-6 text-gray-900 dark:text-gray-100"
>
    <h2 
        @click="toggle()"
        class="mb-4 cursor-pointer border-b border-gray-300 pb-2 text-xl font-bold transition hover:text-blue-600 dark:border-gray-700 dark:hover:text-blue-400"
    >
        Mis Sub cuentas
        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>   

    <div x-show="abierto" x-transition>

        <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-gray-100">Usuarios subordinados de: {{ $jefe->name }}</h2>

        @can('usuarios.configuracion.crear.subordinado')
                    <button
                    class="mb-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    wire:click="showCreateForm"
                >
                    + Nuevo Usuario Subordinado
                </button>
        @endcan


        <div class="overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-900/80">
            <table class="min-w-full rounded-lg border border-collapse border-gray-200 dark:border-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-800/90">
                    <tr>
                        <th class="border-b border-gray-200 px-4 py-2 text-left dark:border-gray-700">Nombre</th>
                        <th class="border-b border-gray-200 px-4 py-2 text-left dark:border-gray-700">Email</th>
                        <th class="border-b border-gray-200 px-4 py-2 text-left dark:border-gray-700">Estado</th>
                        <th class="border-b border-gray-200 px-4 py-2 text-left dark:border-gray-700">Sucursal</th>
                        <th class="border-b border-gray-200 px-4 py-2 text-left dark:border-gray-700">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subordinados as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
                            <td class="border-b border-gray-200 px-4 py-2 dark:border-gray-700">{{ $user->name }}</td>
                            <td class="border-b border-gray-200 px-4 py-2 dark:border-gray-700">{{ $user->email }}</td>
                            
                            <td class="border-b border-gray-200 px-4 py-2 dark:border-gray-700">
                                @if($user->ind_activo)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="border-b border-gray-200 px-4 py-2 dark:border-gray-700">
                                @if($user->sucursal?->nombre)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-indigo-100 text-indigo-700">
                                        {{ $user->sucursal->nombre }}
                                    </span>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="space-x-2 border-b border-gray-200 px-4 py-2 dark:border-gray-700">

                                @can('usuarios.configuracion.editar.subordinado')
                                    <button
                                        class="px-2 py-1 bg-yellow-400 rounded hover:bg-yellow-500"
                                        wire:click="showEditForm({{ $user->id }})"
                                    >
                                        Editar
                                    </button> 
                                @endcan


                                @can('usuarios.configuracion.eliminar.subordinado')
                                                                    {{-- <button
                                    class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700"
                                    wire:click="deleteUser({{ $user->id }})"
                                    onclick="return confirm('¿Eliminar usuario subordinado?')"
                                >
                                    Eliminar
                                </button> --}}

                                        @if($user->ind_activo)
                                        <button
                                            class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700 text-xs sm:text-sm"
                                            wire:click="openDeactivateModal({{ $user->id }})"
                                        >
                                            Inactivar
                                        </button>
                                        @else
                                            <button
                                                class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-700 text-xs sm:text-sm"
                                                wire:click="openActivateModal({{ $user->id }})"
                                            >
                                                Activar
                                            </button>
                                        @endif
                                        
                                @endcan


                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500 dark:text-gray-400">Sin usuarios subordinados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="relative w-full max-w-md rounded-lg bg-white p-6 text-gray-900 shadow-lg sm:p-8 dark:bg-gray-900 dark:text-gray-100">
                <h2 class="mb-4 text-xl font-semibold">
                    {{ $editingId ? 'Editar usuario subordinado' : 'Nuevo usuario subordinado' }}
                </h2>

                <form wire:submit.prevent="saveUser" class="space-y-3">
                    {{-- Nombre (bloqueado si no-admin y ya existe) --}}
                    <div>
                        <label class="mb-1 block font-medium">Nombre </label>
                        <input
                            type="text"
                            wire:model.defer="name"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-800 dark:disabled:text-gray-400"
                            @disabled($nameLocked)
                            required
                        />
                        @if($nameLocked)
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                El nombre no es editable para usuarios sin rol <span class="font-semibold">admin</span>.
                            </p>
                        @endif
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block font-medium">Email *</label>
                        <input type="email" wire:model.defer="email" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" required />
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block font-medium">
                            Contraseña {{ $editingId ? '(solo si quieres cambiarla)' : '*' }}
                        </label>
                        <input
                            type="password"
                            wire:model.defer="password"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            {{ $editingId ? '' : 'required' }}
                        />
                        @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Select de Sucursal --}}
                    <div>
                        <label class="mb-1 block font-medium">Sucursal (opcional)</label>
                        <select
                            wire:model="sucursal_id"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-800 dark:disabled:text-gray-400"
                            @disabled($sucursalLocked)
                        >
                            <option value="">Sin sucursal</option>
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}">
                                    {{ $suc->nombre }} {{ $suc->tipo == 1 ? '(Principal)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if($sucursalLocked)
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                La sucursal no es editable para usuarios sin rol <span class="font-semibold">admin</span>.
                            </p>
                        @endif
                        @error('sucursal_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        @if(!$sucursales->count())
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">No hay sucursales disponibles en la empresa del jefe.</p>
                        @endif
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showForm', false)" class="rounded bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
                    </div>
                </form>

                <button wire:click="$set('showForm', false)" class="absolute right-2 top-2 text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">✕</button>
            </div>
        </div>
        @endif

                {{-- Modal INACTIVAR SUBORDINADO --}}
        <div
            x-cloak
            x-show="showDeactivate"
            x-transition
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/50"
        >
            <div class="relative mx-4 w-full max-w-xl rounded-lg bg-white p-6 text-gray-900 shadow-xl dark:bg-gray-900 dark:text-gray-100">
                <h2 class="text-lg sm:text-xl font-bold mb-4 text-red-600">
                    Confirmar inactivación de usuario
                </h2>

                <p class="mb-4 text-sm text-gray-700 dark:text-gray-300">
                    Estás a punto de inactivar al usuario
                    <span class="font-semibold">{{ $deactivateStats['nombre_usuario'] ?? '' }}</span>.
                </p>

                <div class="mb-4 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                    <p class="font-semibold">
                        Se aplicará lo siguiente:
                    </p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Se inactivará solo este usuario.</li>
                        <li>Proyectos del usuario que se marcarán como inactivos: 
                            <span class="font-semibold">{{ $deactivateStats['total_proyectos'] ?? 0 }}</span>
                        </li>
                        <li>Pedidos del usuario que se marcarán como inactivos: 
                            <span class="font-semibold">{{ $deactivateStats['total_pedidos'] ?? 0 }}</span>
                        </li>
                    </ul>
                </div>

                <p class="mb-4 text-xs text-red-500 dark:text-red-400">
                    Esta acción no elimina registros, pero puede afectar el acceso del usuario y el uso operativo de proyectos y pedidos.
                </p>

                <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2">
                    <button
                        type="button"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-100 sm:w-auto dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800"
                        @click="showDeactivate = false"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700"
                        wire:click="inactivarUsuarioConfirmado"
                    >
                        Sí, inactivar
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal ACTIVAR SUBORDINADO --}}
        <div
            x-cloak
            x-show="showActivate"
            x-transition
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/50"
        >
            <div class="relative mx-4 w-full max-w-xl rounded-lg bg-white p-6 text-gray-900 shadow-xl dark:bg-gray-900 dark:text-gray-100">
                <h2 class="text-lg sm:text-xl font-bold mb-4 text-emerald-700">
                    Confirmar activación de usuario
                </h2>

                <p class="mb-4 text-sm text-gray-700 dark:text-gray-300">
                    Vas a activar la cuenta de
                    <span class="font-semibold">{{ $activateStats['nombre_usuario'] ?? '' }}</span>.
                </p>

                <div class="mb-4 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                    <p class="font-semibold">
                        Se realizará lo siguiente:
                    </p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Se activará únicamente este usuario.</li>
                        <li>Proyectos del usuario que pasarán a activos: 
                            <span class="font-semibold">{{ $activateStats['total_proyectos'] ?? 0 }}</span>
                        </li>
                        <li>Pedidos del usuario que pasarán a activos: 
                            <span class="font-semibold">{{ $activateStats['total_pedidos'] ?? 0 }}</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2">
                    <button
                        type="button"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-100 sm:w-auto dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800"
                        @click="showActivate = false"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                        wire:click="activarUsuarioConfirmado"
                    >
                        Sí, activar
                    </button>
                </div>
            </div>
        </div>


        {{-- Toast Livewire v3 --}}
        <div
            x-data="{ show: false, message: '', type: '' }"
            x-on:notify.window="
                message = $event.detail.message;
                type = $event.detail.type;
                show = true;
                setTimeout(() => show = false, 2200);
            "
            x-show="show"
            x-transition
            class="fixed bottom-6 right-6 z-50 flex min-w-[240px] items-center rounded-lg p-4 shadow-lg"
            :class="type === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200'"
            style="display: none;"
        >
            <span x-text="message"></span>
        </div>

        {{-- Scripts encapsulados --}}
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('notify', (e) => {
                const msg = e.detail?.message || e.detail || 'Acción realizada';
                const toast = document.createElement('div');
                toast.textContent = msg;
                toast.className = 'fixed top-4 right-4 bg-emerald-600 text-white px-4 py-2 rounded-lg shadow z-[100]';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 2500);
            });
        });
        </script>

    </div>
</div>
