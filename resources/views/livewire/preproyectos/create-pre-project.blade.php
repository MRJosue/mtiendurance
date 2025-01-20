<div class="container mx-auto p-6">
    <h2 class="text-2xl font-semibold mb-4">Crear Nuevo Preproyecto</h2>

    <!-- Mensajes de error o éxito -->
    @if (session('message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
            {{ session('message') }}
        </div>
    @elseif (session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="create">
        <!-- Información del Preproyecto -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" wire:model="nombre" class="w-full mt-1 border rounded-lg p-2">
            @error('nombre') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Descripción</label>
            <textarea wire:model="descripcion" class="w-full mt-1 border rounded-lg p-2"></textarea>
            @error('descripcion') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Producción</label>
                <input type="date" wire:model="fecha_produccion" class="w-full mt-1 border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Embarque</label>
                <input type="date" wire:model="fecha_embarque" class="w-full mt-1 border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Entrega</label>
                <input type="date" wire:model="fecha_entrega" class="w-full mt-1 border rounded-lg p-2">
            </div>
        </div>

        <!-- Archivos -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Archivos</label>
            <input type="file" wire:model="files" multiple class="w-full mt-1 border rounded-lg p-2">
            @error('files.*') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Descripciones de Archivos -->
        @foreach ($files as $index => $file)
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Descripción para: {{ $file->getClientOriginalName() }}</label>
                <input type="text" wire:model="fileDescriptions.{{ $index }}" class="w-full mt-1 border rounded-lg p-2">
            </div>
        @endforeach

        <!-- Pedido -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Producto</label>
            <select wire:model="producto_id" class="w-full mt-1 border rounded-lg p-2">
                <option value="">Seleccionar Producto</option>
                @foreach ($productos as $producto)
                    <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                @endforeach
            </select>
            @error('producto_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Total</label>
                <input type="number" wire:model="total" step="0.01" class="w-full mt-1 border rounded-lg p-2">
                @error('total') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Estatus</label>
                <input type="text" wire:model="estatus" class="w-full mt-1 border rounded-lg p-2">
                @error('estatus') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            Crear Preproyecto
        </button>
    </form>
</div>
