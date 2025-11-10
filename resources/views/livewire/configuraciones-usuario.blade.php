<div class="container mx-auto p-6">
    <h2 class="text-xl font-semibold mb-6">Configuraciones del Usuario #{{ $userId }}</h2>

    <form wire:submit.prevent>
        <div class="grid grid-cols-1 gap-6">
            <!-- Checkbox: Puede ser seleccionado -->
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <label class="flex items-center space-x-3">
                    <input
                        type="checkbox"
                        wire:model="flag_user_sel_preproyectos"
                        wire:change="guardarFlag('flag-user-sel-preproyectos', $event.target.checked)"
                        class="rounded border-gray-300"
                    >
                    <span class="text-sm text-gray-700">Puedo ser seleccionado en preproyectos</span>
                </label>
            </div>

            <!-- Checkbox: Puede seleccionar usuarios -->
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <label class="flex items-center space-x-3">
                    <input
                        type="checkbox"
                        wire:model="flag_can_user_sel_preproyectos"
                        wire:change="guardarFlag('flag-can-user-sel-preproyectos', $event.target.checked)"
                        class="rounded border-gray-300"
                    >
                    <span class="text-sm text-gray-700">Puedo seleccionar usuarios para preproyectos</span>
                </label>
            </div>

            {{-- Select de usuarios (condicional) --}}
            @if($flag_can_user_sel_preproyectos)

                @if (session()->has('message'))
                    <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
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
                    class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 space-y-4"
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
                            class="w-full sm:w-auto px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
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
