<x-config-inicial-layout>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 sm:p-8 space-y-6">
        <div>
            <h1 class="text-2xl font-bold mb-1 text-gray-900 dark:text-gray-100">Configuración inicial</h1>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Completa tu información para continuar.
            </p>
        </div>

        @livewire('usuarios.config-inicial', ['userId' => $user->id], key('config-inicial-'.$user->id))
    </div>
</x-config-inicial-layout>
