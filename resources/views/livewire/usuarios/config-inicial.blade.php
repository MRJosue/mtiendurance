<div class="space-y-6">

    {{-- Errores globales (para que “sí se vean”) --}}
    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 text-red-800 p-3 text-sm">
            <p class="font-semibold mb-1">Revisa estos campos:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- =======================
         Datos del usuario
         ======================= --}}
    <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Datos del usuario</h2>
            <span class="text-xs text-gray-500 dark:text-gray-300">
                RFC requerido, contraseña opcional
            </span>
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
    </div>

    {{-- =======================
         Dirección fiscal
         ======================= --}}



    <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-5">


        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Dirección fiscal</h2>
                <p class="text-xs text-gray-500 dark:text-gray-300">Requerida para facturación</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <button
                    type="button"
                    wire:click="crearFiscal"
                    class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                >
                    Nueva dirección fiscal
                </button>

                <div class="flex gap-2 w-full sm:w-auto">
                    <input
                        type="text"
                        wire:model.defer="queryFiscal"
                        placeholder="Buscar por RFC..."
                        class="w-full sm:w-56 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                    <button
                        type="button"
                        wire:click="buscarFiscal"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                    >
                        Buscar
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow">
            <table class="min-w-full border-collapse border border-gray-200 dark:border-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-200">Razón Social</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-200">RFC</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-200">Calle</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-200">Ciudad</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-200">Pred.</th>
                        <th class="border-b px-4 py-2 text-center text-sm font-medium text-gray-600 dark:text-gray-200">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($direccionesFiscales as $dir)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                            <td class="border-b px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $dir->razon_social }}</td>
                            <td class="border-b px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $dir->rfc }}</td>
                            <td class="border-b px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $dir->calle }}</td>
                            <td class="border-b px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $dir->ciudad->nombre ?? '-' }}</td>
                            <td class="border-b px-4 py-2 text-sm text-gray-700 dark:text-gray-200">
                                @if($dir->flag_default)
                                    <span class="text-green-600 font-semibold">Sí</span>
                                @else
                                    <button
                                        type="button"
                                        wire:click="establecerDefaultFiscal({{ $dir->id }})"
                                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs"
                                    >
                                        Establecer
                                    </button>
                                @endif
                            </td>
                            <td class="border-b px-4 py-2 text-sm text-gray-700 dark:text-gray-200">
                                <div class="flex flex-col sm:flex-row gap-2 justify-center">
                                    <button type="button" wire:click="editarFiscal({{ $dir->id }})"
                                        class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">
                                        Editar
                                    </button>
                                    <button type="button" wire:click="borrarFiscal({{ $dir->id }})"
                                        class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-sm text-gray-500 dark:text-gray-300">
                                Sin direcciones fiscales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $direccionesFiscales->links() }}
        </div>
    </div>

    {{-- =======================
         Dirección de entrega
         ======================= --}}
    <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Dirección de entrega</h2>
                <p class="text-xs text-gray-500 dark:text-gray-300">Usada para envíos</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <button
                    type="button"
                    wire:click="crearEntrega"
                    class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                >
                    Nueva dirección
                </button>

                <div class="flex gap-2 w-full sm:w-auto">
                    <input
                        type="text"
                        wire:model.defer="queryEntrega"
                        placeholder="Buscar por contacto..."
                        class="w-full sm:w-56 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                    <button
                        type="button"
                        wire:click="buscarEntrega"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                    >
                        Buscar
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow">
            <table class="min-w-full border-collapse border border-gray-200 dark:border-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-200">Contacto</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-200">Empresa</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-200">Calle</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-200">Ciudad</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-200">Pred.</th>
                        <th class="border-b px-4 py-2 text-center text-sm font-medium text-gray-600 dark:text-gray-200">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($direccionesEntrega as $dir)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                            <td class="border-b px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $dir->nombre_contacto }}</td>
                            <td class="border-b px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $dir->nombre_empresa }}</td>
                            <td class="border-b px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $dir->calle }}</td>
                            <td class="border-b px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $dir->ciudad->nombre ?? '-' }}</td>
                            <td class="border-b px-4 py-2 text-sm text-gray-700 dark:text-gray-200">
                                @if($dir->flag_default)
                                    <span class="text-green-600 font-semibold">Sí</span>
                                @else
                                    <button
                                        type="button"
                                        wire:click="establecerDefaultEntrega({{ $dir->id }})"
                                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs"
                                    >
                                        Establecer
                                    </button>
                                @endif
                            </td>
                            <td class="border-b px-4 py-2 text-sm text-gray-700 dark:text-gray-200">
                                <div class="flex flex-col sm:flex-row gap-2 justify-center">
                                    <button type="button" wire:click="editarEntrega({{ $dir->id }})"
                                        class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">
                                        Editar
                                    </button>
                                    <button type="button" wire:click="borrarEntrega({{ $dir->id }})"
                                        class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-sm text-gray-500 dark:text-gray-300">
                                Sin direcciones de entrega.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $direccionesEntrega->links() }}
        </div>
    </div>

    {{-- =======================
         ÚNICO botón final
         ======================= --}}
    <div class="flex justify-end">
        <button
            type="button"
            wire:click="guardarYContinuar"
            class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
        >
            Guardar y continuar
        </button>
    </div>

    {{-- =======================
         MODAL FISCAL
         ======================= --}}
    @if($modalFiscal)
        <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg w-full max-w-3xl">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 p-4">
                    <h5 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $direccionFiscalId ? 'Editar Dirección Fiscal' : 'Crear Dirección Fiscal' }}
                    </h5>
                    <button class="text-gray-500 hover:text-gray-700" wire:click="$set('modalFiscal', false)">&times;</button>
                </div>

                <div class="p-4 max-h-[75vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">Razón social</label>
                            <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                wire:model.defer="f_razon_social">
                            @error('f_razon_social') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">RFC</label>
                            <input type="text" class="w-full uppercase rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                wire:model.defer="f_rfc">
                            @error('f_rfc') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">Calle</label>
                            <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                wire:model.defer="f_calle">
                            @error('f_calle') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">País</label>
                            <select wire:model.live="f_pais_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Seleccione un País</option>
                                @foreach($f_paisesList as $p)
                                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                            @error('f_pais_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">Estado</label>
                            <select wire:model.live="f_estado_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" @disabled(!$f_pais_id)>
                                <option value="">Seleccione un Estado</option>
                                @foreach($f_estadosList as $e)
                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                @endforeach
                            </select>
                            @error('f_estado_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">Ciudad</label>
                            <select wire:model.live="f_ciudad_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" @disabled(!$f_estado_id)>
                                <option value="">Seleccione una Ciudad</option>
                                @foreach($f_ciudadesList as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                            @error('f_ciudad_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">Código Postal</label>
                            <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                wire:model.defer="f_codigo_postal">
                            @error('f_codigo_postal') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-gray-200 dark:border-gray-700 p-4">
                    <button type="button" wire:click="$set('modalFiscal', false)"
                        class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800">
                        Cancelar
                    </button>
                    <button type="button" wire:click="guardarFiscal"
                        class="px-4 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- =======================
         MODAL ENTREGA
         ======================= --}}
    @if($modalEntrega)
        <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg w-full max-w-4xl">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 p-4">
                    <h5 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $direccionEntregaId ? 'Editar Dirección de Entrega' : 'Crear Dirección de Entrega' }}
                    </h5>
                    <button class="text-gray-500 hover:text-gray-700" wire:click="$set('modalEntrega', false)">&times;</button>
                </div>

                <div class="p-4 max-h-[75vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">Nombre de contacto</label>
                            <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                wire:model.defer="e_nombre_contacto">
                            @error('e_nombre_contacto') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">Empresa</label>
                            <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                wire:model.defer="e_nombre_empresa">
                            @error('e_nombre_empresa') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">Calle</label>
                            <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                wire:model.defer="e_calle">
                            @error('e_calle') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">País</label>
                            <select wire:model.live="e_pais_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Seleccione un País</option>
                                @foreach($e_paisesList as $p)
                                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                            @error('e_pais_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">Estado</label>
                            <select wire:model.live="e_estado_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" @disabled(!$e_pais_id)>
                                <option value="">Seleccione un Estado</option>
                                @foreach($e_estadosList as $e)
                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                @endforeach
                            </select>
                            @error('e_estado_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">Ciudad</label>
                            <select wire:model.live="e_ciudad_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" @disabled(!$e_estado_id)>
                                <option value="">Seleccione una Ciudad</option>
                                @foreach($e_ciudadesList as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                            @error('e_ciudad_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">Código Postal</label>
                            <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                wire:model.defer="e_codigo_postal">
                            @error('e_codigo_postal') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-200 mb-1">Teléfono</label>
                            <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                wire:model.defer="e_telefono">
                            @error('e_telefono') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-gray-200 dark:border-gray-700 p-4">
                    <button type="button" wire:click="$set('modalEntrega', false)"
                        class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800">
                        Cancelar
                    </button>
                    <button type="button" wire:click="guardarEntrega"
                        class="px-4 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Sin listeners por ahora, solo cumpliendo encapsulado.
            });
        </script>
    @endpush
</div>
