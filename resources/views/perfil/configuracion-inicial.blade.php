<x-config-inicial-layout>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 sm:p-8 space-y-6">
        <div>
            <h1 class="text-2xl font-bold mb-1 text-gray-900 dark:text-gray-100">Configuración inicial</h1>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Completa tu información para continuar.
            </p>
        </div>

        {{-- Datos del usuario (componente individual) --}}
        <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
            @livewire('usuarios.perfil-datos-usuario', ['userId' => $user->id], key('perfil-datos-'.$user->id))
        </div>

        {{-- Direcciones --}}
        <div class="space-y-6">

            {{-- Dirección fiscal --}}
            <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                        Dirección fiscal
                    </h2>
                    <span class="text-xs text-gray-500 dark:text-gray-300">
                        Requerida para facturación
                    </span>
                </div>

                @livewire('usuarios.direcciones-fiscales-crud', ['userId' => $user->id], key('fiscal-'.$user->id))
            </div>

            {{-- Dirección de entrega --}}
            <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                        Dirección de entrega
                    </h2>
                    <span class="text-xs text-gray-500 dark:text-gray-300">
                        Usada para envíos
                    </span>
                </div>

                @livewire('usuarios.direcciones-entrega-crud', ['userId' => $user->id], key('entrega-'.$user->id))
            </div>

        </div>

        {{-- Botón Finalizar (ya no es submit, ahora es una ruta POST/GET separada o Livewire) --}}
        <div class="flex justify-end">
            <form method="POST" action="{{ route('perfil.inicial.finalizar') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                >
                    Guardar y continuar
                </button>
            </form>
        </div>
    </div>
</x-config-inicial-layout>
