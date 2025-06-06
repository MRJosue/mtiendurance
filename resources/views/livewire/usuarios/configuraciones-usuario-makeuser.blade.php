<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('configuracionesusuariosucursal') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('configuracionesusuariosucursal', JSON.stringify(this.abierto));
        }
    }"
    class="container mx-auto p-6"
>
            <h2 
                @click="toggle()"
                class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
            >
                Mis Sub cuentas
            <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
            </h2>   

<!-- Contenido del panel -->
    <div x-show="abierto" x-transition>

            <h2 class="text-xl font-semibold mb-4">Usuarios subordinados de: {{ $jefe->name }}</h2>

            <!-- Botón para nuevo usuario -->
            <button class="mb-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                wire:click="showCreateForm">
                + Nuevo Usuario Subordinado
            </button>

            <!-- Tabla de usuarios subordinados -->
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border-b px-4 py-2 text-left">Nombre</th>
                            <th class="border-b px-4 py-2 text-left">Email</th>
                            <th class="border-b px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subordinados as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="border-b px-4 py-2">{{ $user->name }}</td>
                                <td class="border-b px-4 py-2">{{ $user->email }}</td>
                                <td class="border-b px-4 py-2 space-x-2">
                                    <button class="px-2 py-1 bg-yellow-400 rounded hover:bg-yellow-500"
                                        wire:click="showEditForm({{ $user->id }})">Editar</button>
                                    <button class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700"
                                        wire:click="deleteUser({{ $user->id }})"
                                        onclick="return confirm('¿Eliminar usuario subordinado?')">Eliminar</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-gray-500 py-4">Sin usuarios subordinados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Formulario crear/editar -->
            @if($showForm)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
                <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md relative">
                    <h2 class="text-xl font-semibold mb-4">
                        {{ $editingId ? 'Editar usuario subordinado' : 'Nuevo usuario subordinado' }}
                    </h2>
                    <form wire:submit.prevent="saveUser">
                        <div class="mb-3">
                            <label class="block mb-1 font-medium">Nombre *</label>
                            <input type="text" wire:model.defer="name" class="w-full border rounded-lg px-3 py-2" required />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="block mb-1 font-medium">Email *</label>
                            <input type="email" wire:model.defer="email" class="w-full border rounded-lg px-3 py-2" required />
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="block mb-1 font-medium">Contraseña {{ $editingId ? '(solo si quieres cambiarla)' : '*' }}</label>
                            <input type="password" wire:model.defer="password" class="w-full border rounded-lg px-3 py-2" {{ $editingId ? '' : 'required' }}/>
                            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancelar</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
                        </div>
                    </form>
                    <button wire:click="$set('showForm', false)" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">✕</button>
                </div>
            </div>
            @endif

            <!-- Notificaciones Livewire/AlpineJS -->
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
                class="fixed bottom-6 right-6 z-50 min-w-[240px] flex items-center p-4 rounded-lg"
                :class="type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                style="display: none;"
            >
                <span x-text="message"></span>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {});
            </script>
    </div>
</div>
