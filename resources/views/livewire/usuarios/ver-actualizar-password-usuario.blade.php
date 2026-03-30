<div
    x-data="{ showNewPassword: false }"
    class="container mx-auto p-6 text-gray-900 dark:text-gray-100"
>
    <div class="space-y-4 rounded-lg bg-white p-6 shadow dark:bg-gray-900/80">

        {{-- Encabezado --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                    Contraseña del usuario
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Usuario #{{ $user->id }} — {{ $user->name }} ({{ $user->email }})
                </p>
            </div>

            <div>
                @if($user->ind_activo)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                        Usuario activo
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                        Usuario inactivo
                    </span>
                @endif
            </div>
        </div>

        {{-- Mensajes de sesión --}}
        @if (session()->has('password_message'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
                {{ session('password_message') }}
            </div>
        @endif

        {{-- Bloque: "contraseña actual" (no se muestra el valor real) --}}
        <div class="space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/70">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                Contraseña actual
            </p>
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <input
                    type="password"
                    value="************"
                    class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-gray-500 sm:w-64 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                    disabled
                >
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Por seguridad, la contraseña actual no se puede ver ni recuperar.
                    Solo puedes cambiarla por una nueva.
                </p>
            </div>
        </div>

        {{-- Formulario para asignar nueva contraseña --}}
        <form wire:submit.prevent="updatePassword" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Nueva contraseña --}}
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nueva contraseña
                    </label>
                    <div class="relative">
                        <input
                            :type="showNewPassword ? 'text' : 'password'"
                            wire:model.live="new_password"
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                        >
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 px-3 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            @click="showNewPassword = !showNewPassword"
                        >
                            <span x-show="!showNewPassword">Ver</span>
                            <span x-show="showNewPassword">Ocultar</span>
                        </button>
                    </div>
                    @error('new_password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Mínimo 8 caracteres. Procura usar números y letras.
                    </p>
                </div>

                {{-- Confirmación --}}
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Confirmar nueva contraseña
                    </label>
                    <input
                        :type="showNewPassword ? 'text' : 'password'"
                        wire:model.live="new_password_confirmation"
                        autocomplete="new-password"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                    >
                    @error('new_password_confirmation')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Generar contraseña aleatoria --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <button
                    type="button"
                    wire:click="generateRandomPassword"
                    class="w-full rounded-lg bg-gray-800 px-4 py-2 text-white hover:bg-gray-900 sm:w-auto dark:bg-gray-700 dark:hover:bg-gray-600"
                >
                    Generar contraseña aleatoria
                </button>

                @if($generatedPassword)
                    <div class="flex-1 rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2 text-xs text-yellow-800 dark:border-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-200">
                        <span class="font-semibold">Nueva contraseña generada:</span>
                        <span class="font-mono break-all">{{ $generatedPassword }}</span>
                        <p class="mt-1">
                            Copia esta contraseña y compártela con el usuario.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Botón guardar --}}
            <div class="flex flex-col sm:flex-row justify-end gap-2 mt-4">
                <button
                    type="submit"
                    class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Actualizar contraseña</span>
                    <span wire:loading>Guardando...</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Script encapsulado, por si quieres escuchar el evento desde JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('password-actualizada', (event) => {
                console.log('Password actualizada para el usuario', event.detail.id);
            });
        });
    </script>
</div>
