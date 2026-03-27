@if($mostrarModalDireccion)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-lg rounded-lg bg-white shadow">
            <div class="flex items-center justify-between border-b p-4">
                <h3 class="text-lg font-semibold">
                    {{ $tipoDireccion === 'fiscal' ? 'Nueva Dirección Fiscal' : 'Nueva Dirección de Entrega' }}
                </h3>
                <button class="text-gray-500 hover:text-gray-700" wire:click="cerrarModalDireccion">&times;</button>
            </div>

            <div class="space-y-4 p-4">
                @if($tipoDireccion === 'fiscal')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">RFC</label>
                        <input type="text" class="w-full rounded-lg border p-2" wire:model.defer="formDireccion.rfc">
                        @error('formDireccion.rfc') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre de Contacto</label>
                        <input type="text" class="w-full rounded-lg border p-2" wire:model.defer="formDireccion.nombre_contacto">
                        @error('formDireccion.nombre_contacto') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre de Empresa</label>
                        <input type="text" class="w-full rounded-lg border p-2" wire:model.defer="formDireccion.nombre_empresa">
                        @error('formDireccion.nombre_empresa') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="text" class="w-full rounded-lg border p-2" wire:model.defer="formDireccion.telefono">
                        @error('formDireccion.telefono') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Calle</label>
                    <input type="text" class="w-full rounded-lg border p-2" wire:model.defer="formDireccion.calle">
                    @error('formDireccion.calle') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">País</label>
                        <select class="w-full rounded-lg border p-2" wire:model="formDireccion.pais_id" wire:change="onPaisChange">
                            <option value="">Seleccione</option>
                            @foreach($paises as $pais)
                                <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                            @endforeach
                        </select>
                        @error('formDireccion.pais_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                        <select class="w-full rounded-lg border p-2" wire:model="formDireccion.estado_id" wire:change="onEstadoChange">
                            <option value="">Seleccione</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                            @endforeach
                        </select>
                        @error('formDireccion.estado_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ciudad</label>
                        <input
                            type="text"
                            class="w-full rounded-lg border p-2"
                            wire:model.defer="formDireccion.ciudad"
                            placeholder="Ej. Puebla"
                            @disabled(!($formDireccion['estado_id'] ?? null))
                        >
                        @error('formDireccion.ciudad') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Código Postal</label>
                        <input type="text" class="w-full rounded-lg border p-2" wire:model.defer="formDireccion.codigo_postal">
                        @error('formDireccion.codigo_postal') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-6 flex items-center gap-2">
                        <input id="flag_default" type="checkbox" class="rounded" wire:model="formDireccion.flag_default">
                        <label for="flag_default" class="text-sm text-gray-700">Marcar como predeterminada</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t p-4">
                <button type="button" class="rounded bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300" wire:click="cerrarModalDireccion">Cancelar</button>
                <button type="button" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" wire:click="guardarDireccion">Guardar</button>
            </div>
        </div>
    </div>
@endif
