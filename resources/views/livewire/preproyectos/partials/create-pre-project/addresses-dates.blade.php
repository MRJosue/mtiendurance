<div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-slate-800">Direcciones y fechas</h3>
            <p class="text-xs text-slate-500">Configura facturación, entrega, tipo de envío y fechas clave del proyecto.</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
            Planeación
        </span>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
            <div class="flex items-center justify-between gap-3">
                <label class="block text-sm font-medium text-slate-700">Dirección Fiscal</label>
                <button
                    type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100"
                    wire:click="abrirModalDireccion('fiscal')"
                    title="Nueva dirección fiscal"
                >
                    <span class="text-lg leading-none">+</span>
                </button>
            </div>

            <select wire:key="direccion-fiscal-{{ $selectedUserId ?: 'none' }}" wire:model="direccion_fiscal_id" class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Seleccionar Dirección Fiscal</option>
                @foreach ($direccionesFiscales as $direccion)
                    <option value="{{ $direccion->id }}">
                        {{ $direccion->rfc ?? '—' }} — {{ $direccion->calle }}
                    </option>
                @endforeach
            </select>
            @error('direccion_fiscal_id') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
            <div class="flex items-center justify-between gap-3">
                <label class="block text-sm font-medium text-slate-700">Dirección de Entrega</label>
                <button
                    type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100"
                    wire:click="abrirModalDireccion('entrega')"
                    title="Nueva dirección de entrega"
                >
                    <span class="text-lg leading-none">+</span>
                </button>
            </div>

            <select wire:key="direccion-entrega-{{ $selectedUserId ?: 'none' }}" wire:change="cargarTiposEnvio" wire:model="direccion_entrega_id" class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Selecciona una dirección</option>
                @foreach ($direccionesEntrega as $direccion)
                    <option value="{{ $direccion->id }}">
                        {{ $direccion->nombre_contacto ?? '—' }} — {{ $direccion->calle }}
                    </option>
                @endforeach
            </select>
            @error('direccion_entrega_id') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
            <label class="block text-sm font-medium text-slate-700">Tipo de Envío</label>
            <select
                wire:key="tipo-envio-{{ $direccion_entrega_id ?: 'none' }}"
                wire:model="id_tipo_envio"
                wire:change="on_Calcula_Fechas_Entrega"
                class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                @disabled(!$direccion_entrega_id)
            >
                <option value="">Selecciona un tipo de envío</option>
                @foreach ($tiposEnvio as $envio)
                    <option value="{{ $envio->id }}">{{ $envio->nombre }} ({{ $envio->dias_envio }} días)</option>
                @endforeach
            </select>
            @error('id_tipo_envio') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
            <label class="block text-sm font-medium text-slate-700">Fecha Producción</label>
            <input type="date" wire:model="fecha_produccion" class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm" readonly>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
            <label class="block text-sm font-medium text-slate-700">Fecha Embarque</label>
            <input type="date" wire:model="fecha_embarque" class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm" readonly>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
            <label class="block text-sm font-medium text-slate-700">Fecha Entrega</label>
            <input
                wire:change="validarFechaEntrega"
                wire:model="fecha_entrega"
                type="date"
                class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                min="{{ date('Y-m-d') }}"
                id="fechaEntrega"
            >
        </div>

        {{-- <div class="rounded-xl bg-slate-900 px-4 py-3 text-white shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-200">Resumen</p>
            <div class="mt-3 space-y-2 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-slate-300">Entrega</span>
                    <span class="font-semibold">{{ $fecha_entrega ?: 'Pendiente' }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-slate-300">Envío</span>
                    <span class="font-semibold">{{ $id_tipo_envio ? 'Definido' : 'Sin definir' }}</span>
                </div>
            </div>
        </div> --}}
    </div>

    @error('error') <span class="mt-3 block text-sm text-red-600">{{ $message }}</span> @enderror

    @if ($mensaje_produccion)
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            {{ $mensaje_produccion }}
        </div>
    @endif
</div>
