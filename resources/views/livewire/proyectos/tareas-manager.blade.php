<div class="p-4 bg-gray-100 min-h-screen">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Administrar Tareas</h2>

    @if (session()->has('message'))
        <div class="p-4 mb-4 bg-green-100 border border-green-400 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="{{ $editingTarea ? 'updateTarea' : 'saveTarea' }}" class="bg-white shadow rounded p-6 mb-6">
        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700 font-medium mb-2">Descripción</label>
            <textarea id="descripcion" wire:model="descripcion" class="w-full border-gray-300 rounded shadow-sm focus:ring focus:ring-blue-300" placeholder="Escribe la descripción"></textarea>
            @error('descripcion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label for="staffId" class="block text-gray-700 font-medium mb-2">Asignar a</label>
            <select id="staffId" wire:model="staffId" class="w-full border-gray-300 rounded shadow-sm focus:ring focus:ring-blue-300">
                <option value="">Selecciona un usuario</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                @endforeach
            </select>
            @error('staffId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label for="estado" class="block text-gray-700 font-medium mb-2">Estado</label>
            <select id="estado" wire:model="estado" class="w-full border-gray-300 rounded shadow-sm focus:ring focus:ring-blue-300">
                <option value="PENDIENTE">PENDIENTE</option>
                <option value="EN PROCESO">EN PROCESO</option>
                <option value="COMPLETADA">COMPLETADA</option>
            </select>
            @error('estado') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="w-full py-2 bg-blue-600 text-white font-medium rounded shadow hover:bg-blue-700">
            {{ $editingTarea ? 'Actualizar' : 'Crear' }} Tarea
        </button>
    </form>

    <h3 class="text-xl font-bold text-gray-800 mb-4">Tareas</h3>
    <ul class="space-y-4">
        @foreach ($tareas as $tarea)
            <li class="bg-white shadow rounded p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <div>
                    <p class="text-lg font-medium text-gray-800">{{ $tarea['descripcion'] }}</p>
                    <p class="text-sm text-gray-600">Estado: <span class="font-bold">{{ $tarea['estado'] }}</span></p>
                    <p class="text-sm text-gray-600">Asignado a: {{ $tarea['staff']['name'] ?? 'Sin asignar' }}</p>
                </div>
                <div class="flex space-x-2 mt-2 sm:mt-0">
                    <button wire:click="editTarea({{ $tarea['id'] }})" class="px-4 py-2 bg-yellow-500 text-white rounded shadow hover:bg-yellow-600">Editar</button>
                    <button wire:click="deleteTarea({{ $tarea['id'] }})" class="px-4 py-2 bg-red-500 text-white rounded shadow hover:bg-red-600">Eliminar</button>
                </div>
            </li>
        @endforeach
    </ul>
</div>
