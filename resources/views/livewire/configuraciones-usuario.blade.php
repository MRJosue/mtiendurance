<div class="container mx-auto p-6 text-gray-900 dark:text-gray-100">
    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">Configuraciones del Usuario #{{ $userId }}</h2>

    <form wire:submit.prevent>
        <div class="grid grid-cols-1 gap-6">
            <!-- Checkbox: Puede ser seleccionado -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/80">
                <label class="flex items-center space-x-3">
                    <input
                        type="checkbox"
                        wire:model="flag_user_sel_preproyectos"
                        wire:change="guardarFlag('flag-user-sel-preproyectos', $event.target.checked)"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                    >
                    <span class="text-sm text-gray-700 dark:text-gray-200">Puedo ser seleccionado en preproyectos</span>
                </label>
            </div>

            <!-- Checkbox: Puede seleccionar usuarios -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/80">
                <label class="flex items-center space-x-3">
                    <input
                        type="checkbox"
                        wire:model="flag_can_user_sel_preproyectos"
                        wire:change="guardarFlag('flag-can-user-sel-preproyectos', $event.target.checked)"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                    >
                    <span class="text-sm text-gray-700 dark:text-gray-200">Puedo seleccionar usuarios para preproyectos</span>
                </label>
            </div>

            {{-- Select de usuarios (condicional) --}}
            @if($flag_can_user_sel_preproyectos)

                @if (session()->has('message'))
                    <div class="mb-4 rounded bg-green-100 px-4 py-2 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                        {{ session('message') }}
                    </div>
                @endif

                <div 
                    x-data="{
                        seleccionados: @entangle('usuariosSeleccionados').live,
                        inicial: @js($usuariosSeleccionados),
                        get hayCambios() {
                            // compara arrays sin importar orden
                            const a = [...this.seleccionados].map(Number).sort((x,y)=>x-y);
                            const b = [...this.inicial].map(Number).sort((x,y)=>x-y);
                            if (a.length !== b.length) return true;
                            for (let i=0;i<a.length;i++){ if (a[i] !== b[i]) return true; }
                            return false;
                        }
                    }"
                    class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/80"
                >
                    <div class="flex items-center space-x-3">
                        <x-select-multiple-usuarios
                            label="Usuarios permitidos"
                            :opciones="$todosLosUsuarios->toArray()"
                            entangle="usuariosSeleccionados"
                            :seleccionados="$usuariosSeleccionados"
                        />
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!hayCambios"
                            wire:click="guardarUsuariosPermitidos"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="guardarUsuariosPermitidos">Guardar usuarios permitidos</span>
                            <span wire:loading wire:target="guardarUsuariosPermitidos">Guardando…</span>
                        </button>

                        <button
                            type="button"
                            class="w-full rounded-lg bg-gray-100 px-4 py-2 text-gray-700 hover:bg-gray-200 sm:w-auto dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                            @click="seleccionados = [...inicial]"
                        >
                            Revertir cambios
                        </button>
                    </div>
                </div>

            @endif



            
        </div>
    </form>

        @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('notify', (e) => {
                const { message = 'Acción realizada correctamente.', type = 'success' } = e.detail || {};
                // Toast básico con Tailwind
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg shadow
                    ${type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'}`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            });
        });
        </script>
        @endpush
</div>
