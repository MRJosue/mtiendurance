<x-config-inicial-layout>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 sm:p-8 space-y-6">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Configuración inicial</h1>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Completa tu información para continuar usando el sistema.
                </p>
            </div>

            <span class="inline-flex items-center rounded-full bg-yellow-100 text-yellow-800 px-3 py-1 text-xs font-semibold
                         dark:bg-yellow-900/30 dark:text-yellow-200">
                Requerido
            </span>
        </div>

        {{-- FORM: Datos básicos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nombre</label>
                <input type="text" wire:model.defer="name"
                       class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
                <input type="email" wire:model.defer="email"
                       class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">RFC</label>
                <input type="text" wire:model.defer="rfc" maxlength="13"
                       class="mt-1 w-full uppercase rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                       placeholder="XAXX010101000">
                @error('rfc') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                    Cambiar contraseña (opcional)
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nueva contraseña</label>
                        <input type="password" wire:model.defer="password"
                               class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Confirmar contraseña</label>
                        <input type="password" wire:model.defer="password_confirmation"
                               class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        @error('password_confirmation') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- DIRECCIONES --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Dirección fiscal</h2>
                    <span class="text-xs text-gray-500 dark:text-gray-300">Crea o edita al menos una</span>
                </div>

                @livewire('usuarios.direcciones-fiscales-crud', ['userId' => auth()->id()], key('fiscal-'.auth()->id()))
            </div>

            <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Dirección de entrega</h2>
                    <span class="text-xs text-gray-500 dark:text-gray-300">Crea o edita al menos una</span>
                </div>

                @livewire('usuarios.direcciones-entrega-crud', ['userId' => auth()->id()], key('entrega-'.auth()->id()))
            </div>
        </div>

        {{-- Acciones --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 pt-2">
            <button
                wire:click="guardar"
                class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
            >
                Guardar y continuar
            </button>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Placeholder por si luego quieres listeners/validaciones front
            });
        </script>
    @endpush
</x-config-inicial-layout>
