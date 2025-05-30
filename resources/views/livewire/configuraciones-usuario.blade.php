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

            <!-- Select de usuarios (condicional) -->
            @if($flag_can_user_sel_preproyectos)

            @if (session()->has('message'))
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
                    {{ session('message') }}
                </div>
            @endif

            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-20 gap-4 ">
                <div class="flex items-center space-x-3 p-1">
                    <x-select-multiple-usuarios
                        label="Usuarios permitidos"
                        :opciones="$todosLosUsuarios->toArray()"
                        
                        entangle="usuariosSeleccionados"
                        :seleccionados="$usuariosSeleccionados"
                    />
                </div>

                <div class="flex items-center space-x-3 p-1">
                <x-button 
                    positive 
                    label="Asignar"
                    wire:click="guardarUsuariosPermitidos"
                />
                </div>
            </div>


            @endif


            
        </div>
    </form>
</div>
