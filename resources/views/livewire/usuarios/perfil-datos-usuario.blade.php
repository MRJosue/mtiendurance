<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Datos del usuario</h2>

        <button
            type="button"
            wire:click="guardar"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
        >
            Guardar datos
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nombre</label>
            <input
                type="text"
                wire:model.defer="name"
                readonly
                class="mt-1 w-full rounded-lg border-gray-300 bg-gray-100 cursor-not-allowed
                    dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
            >
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
            <input
                type="email"
                wire:model.defer="email"
                readonly
                class="mt-1 w-full rounded-lg border-gray-300 bg-gray-100 cursor-not-allowed
                    dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
            >
        </div>


        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">RFC</label>
            <input
                type="text"
                wire:model.defer="rfc"
                maxlength="13"
                class="mt-1 w-full uppercase rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                placeholder="XAXX010101000"
            >
            @error('rfc') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4">
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                Cambiar contraseña (opcional)
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nueva contraseña</label>
                    <input
                        type="password"
                        wire:model.defer="password"
                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                    @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Confirmar contraseña</label>
                    <input
                        type="password"
                        wire:model.defer="password_confirmation"
                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                    @error('password_confirmation') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Placeholder: por si luego quieres listeners adicionales
            });
        </script>
    @endpush
</div>
