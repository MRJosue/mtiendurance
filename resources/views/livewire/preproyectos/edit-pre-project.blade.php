<div class="container mx-auto max-w-7xl p-4 sm:p-6">
    <div class="mb-5 rounded-3xl border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-blue-900 px-5 py-5 text-white shadow-lg">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-200">Preproyectos</p>
                <h2 class="mt-1 text-2xl font-semibold">Editar Preproyecto</h2>
                <p class="mt-1 text-sm text-slate-200">Actualiza la informacion principal del proyecto, sus opciones, direcciones y archivos desde un solo flujo.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs font-medium text-slate-100">
                <span class="rounded-full bg-white/10 px-3 py-1 backdrop-blur">Formulario guiado</span>
                <span class="rounded-full bg-white/10 px-3 py-1 backdrop-blur">Archivos existentes</span>
                <span class="rounded-full bg-white/10 px-3 py-1 backdrop-blur">Pre-aprobacion</span>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm">
            {{ session('message') }}
        </div>
    @elseif (session()->has('error'))
        <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="preguardado" class="space-y-5">
        <div
            x-data="{
                open: false,
                search: @entangle('usuarioQuery').live,
                selectedId: @entangle('usuario_id_nuevo'),
                puedeBuscar: @js($puedeBuscarUsuarios) && !@js($modoLectura),
                get hasResults(){ return (this.$wire.usuariosSugeridos || []).length > 0 },
                select(id){
                    this.selectedId = id;
                    const user = (this.$wire.usuariosSugeridos || []).find(u => u.id === id);
                    this.search = user ? user.name + ' (' + user.email + ')' : '';
                    this.open = false;
                },
                init(){
                    if(this.selectedId){
                        const user = (this.$wire.usuariosSugeridos || []).find(u => u.id === this.selectedId);
                        if(user){
                            this.search = user.name + ' (' + user.email + ')';
                        }
                    }
                }
            }"
            class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
        >
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Datos generales del preproyecto</h3>
                    <p class="text-xs text-slate-500">Define al cliente, la informacion base y el producto a desarrollar.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                    Edicion
                </span>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <div class="xl:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Seleccionar Usuario</label>
                    <input
                        x-model="search"
                        @focus="open = puedeBuscar"
                        @click.outside="open = false"
                        placeholder="Buscar por nombre o email..."
                        class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        :readonly="!puedeBuscar"
                        :class="{ 'bg-slate-100 text-slate-500 cursor-not-allowed': !puedeBuscar }"
                        type="text"
                    />

                    <div x-show="open && puedeBuscar" x-transition class="relative">
                        <div class="absolute z-50 mt-1 max-h-56 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg">
                            <template x-for="user in ($wire.usuariosSugeridos || [])" :key="user.id">
                                <div
                                    @click="select(user.id)"
                                    class="flex cursor-pointer items-center justify-between px-3 py-2 text-sm hover:bg-blue-100"
                                    :class="{ 'bg-blue-50': user.id === selectedId }"
                                >
                                    <div class="truncate">
                                        <span class="font-medium" x-text="user.name"></span>
                                        <span class="ml-1 text-xs text-slate-500" x-text="'(' + user.email + ')'"></span>
                                    </div>
                                </div>
                            </template>

                            <div x-show="!hasResults" class="px-3 py-2 text-sm italic text-slate-500">
                                No se encontraron usuarios CLIENTE
                            </div>
                        </div>
                    </div>

                    <div class="mt-2" x-show="selectedId">
                        <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-sm text-blue-700">
                            Usuario seleccionado:
                            <span
                                x-text="(() => {
                                    const lista = ($wire.usuariosSugeridos || []);
                                    const u = lista.find(u => u.id === selectedId);
                                    return u ? `${u.name} (${u.email})` : (search || `ID ${selectedId}`);
                                })()"
                            ></span>
                        </span>
                    </div>

                    @error('UsuarioSeleccionado')
                        <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Nombre</label>
                    <input type="text" wire:model="nombre" class="mt-1 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" @readonly($modoLectura)>
                    @error('nombre') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Categoria</label>
                    <select wire:change="onCategoriaChange" wire:model="categoria_id" class="mt-1 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" @disabled($modoLectura)>
                        <option value="">Seleccionar Categoria</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="xl:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Descripcion</label>
                    <textarea wire:model="descripcion" rows="3" class="mt-1 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" @readonly($modoLectura)></textarea>
                    @error('descripcion') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Producto</label>
                    <select wire:change="onProductoChange" wire:model="producto_id" class="mt-1 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" @disabled($modoLectura)>
                        <option value="">Seleccionar Producto</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                        @endforeach
                    </select>
                    @error('producto_id') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-3">
                    @if($producto_id)
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $flag_requiere_proveedor ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $flag_requiere_proveedor ? 'Este producto REQUIERE proveedor' : 'Este producto NO requiere proveedor' }}
                        </span>
                    @endif

                    @if ($mostrar_selector_armado)
                        <div>
                            <label class="block text-sm font-medium text-slate-700">El proyecto sera armado?</label>
                            <select wire:model="seleccion_armado" wire:change="despligaformopciones" class="mt-1 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" @disabled($modoLectura)>
                                <option value="">Seleccionar</option>
                                <option value="1">Si</option>
                                <option value="0">No</option>
                            </select>
                            @error('seleccion_armado') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <label class="block text-sm font-semibold text-slate-800">Caracteristicas y Opciones</label>
                    <p class="text-xs text-slate-500">Selecciona las opciones necesarias para cada caracteristica del producto.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                    {{ count($caracteristicas_sel) }} caracteristicas
                </span>
            </div>

            <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
                @foreach ($caracteristicas_sel as $index => $caracteristica)
                    @php
                        $opciones = \App\Models\Opcion::where('ind_activo', 1)
                            ->whereHas('caracteristicas', function ($query) use ($caracteristica) {
                                $query->where('caracteristica_id', $caracteristica['id']);
                            })->get();
                    @endphp

                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800">{{ $caracteristica['nombre'] }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $opciones->count() === 1 ? 'Opcion asignada automaticamente' : 'Selecciona una o varias opciones' }}
                                </p>
                            </div>
                            <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-[11px] font-medium text-slate-500 ring-1 ring-slate-200">
                                {{ count($caracteristica['opciones'] ?? []) }} seleccionadas
                            </span>
                        </div>

                        @if ($opciones->count() === 1)
                            <div class="mt-3 inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">
                                {{ $opciones->first()->nombre }} ({{ $opciones->first()->valoru }})
                            </div>
                        @else
                            <div class="mt-3">
                                <select wire:key="edit-prod-{{ $producto_id }}-car-{{ $index }}" wire:change="addOpcion({{ $index }}, $event.target.value)" class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" @disabled($modoLectura)>
                                    <option value="">Seleccionar opcion</option>
                                    @foreach ($opciones as $opcion)
                                        <option value="{{ $opcion->id }}">{{ $opcion->nombre }} ({{ $opcion->valoru }})</option>
                                    @endforeach
                                </select>
                            </div>

                            @if(!empty($caracteristica['opciones']))
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($caracteristica['opciones'] as $opcionIndex => $opcion)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-700 ring-1 ring-slate-200">
                                            <span>{{ $opcion['nombre'] }} ({{ $opcion['valoru'] }})</span>
                                            @hasanyrole('admin|cliente')
                                                <button type="button" wire:click="removeOpcion({{ $index }}, {{ $opcionIndex }})" class="rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-600 hover:bg-rose-100" @disabled($modoLectura)>
                                                    Quitar
                                                </button>
                                            @endhasanyrole
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-3 space-y-1">
                @error('opciones_sel')
                    <span class="block text-sm text-red-600">{{ $message }}</span>
                @enderror

                @error('caracteristicas_sel')
                    <span class="block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div wire:key="tallas-{{ $producto_id }}" class="mb-5 space-y-4">
            @if ($mostrarFormularioTallas)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Cantidad por Tallas</h3>
                            <p class="text-xs text-slate-500">Captura cantidades por grupo de tallas de forma rapida.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                            {{ $tallas->count() }} tallas disponibles
                        </span>
                    </div>

                    <div class="space-y-4">
                        @foreach ($tallas->flatMap->gruposTallas->unique('id') as $grupoTalla)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                                <p class="mb-3 text-sm font-semibold text-slate-700">{{ $grupoTalla->nombre }}</p>

                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                    @foreach ($tallas->filter(fn($talla) => $talla->gruposTallas->contains('id', $grupoTalla->id)) as $talla)
                                        <label class="flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2 ring-1 ring-slate-200">
                                            <span class="text-sm font-medium text-slate-700">{{ $talla->nombre }}</span>
                                            <input type="number" wire:model.defer="tallasSeleccionadas.{{ $grupoTalla->id }}.{{ $talla->id }}" class="w-24 rounded-lg border-slate-300 px-3 py-1.5 text-right text-sm focus:border-blue-500 focus:ring-blue-500" min="0" value="{{ $tallasSeleccionadas[$grupoTalla->id][$talla->id] ?? 0 }}" @readonly($modoLectura)>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-slate-800">Total de Piezas</label>
                        <p class="text-xs text-slate-500">Resumen total esperado para este preproyecto.</p>
                        <input type="number" wire:model="total_piezas" class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" min="1" @readonly($modoLectura)>
                    </div>
                    <div class="rounded-xl bg-slate-100 px-4 py-3 text-center sm:min-w-[150px]">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Total actual</p>
                        <p class="text-2xl font-semibold text-slate-800">{{ $total_piezas ?: 0 }}</p>
                    </div>
                </div>
                @error('total_piezas') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <label class="block text-sm font-semibold text-slate-800">Archivos de apoyo</label>
                    <p class="text-xs text-slate-500">Agrega nuevos archivos y consulta los ya cargados en el preproyecto.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                    Historial disponible
                </span>
            </div>

            <div x-data="{ isUploading: @entangle('isUploading') }">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Seleccionar archivos</label>
                        <input type="file" wire:model="files" multiple accept=".zip,.jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx" class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" @disabled($modoLectura)>
                        @error('files.*')
                            <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <span x-show="isUploading" class="inline-flex items-center text-sm text-blue-600">
                            Subiendo...
                            <svg class="ml-2 inline h-4 w-4 animate-spin" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                        </span>
                    </div>
                </div>

                @foreach ($files as $index => $file)
                    <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                        <p class="text-sm font-semibold text-slate-700">{{ $file->getClientOriginalName() }}</p>
                        <label class="mt-2 block text-xs font-medium uppercase tracking-wide text-slate-500">Descripcion</label>
                        <input type="text" wire:model="fileDescriptions.{{ $index }}" class="mt-1 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" @readonly($modoLectura)>
                    </div>
                @endforeach
            </div>

            @if ($uploadedFiles)
                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-slate-800">Vista previa de archivos nuevos</h3>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
                            {{ count($uploadedFiles) }} cargados
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($uploadedFiles as $file)
                            <div class="flex items-center gap-3 rounded-xl bg-white p-3 ring-1 ring-slate-200">
                                @if ($file['preview'])
                                    <img src="{{ $file['preview'] }}" class="h-14 w-14 rounded-lg object-cover">
                                @else
                                    <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-slate-700">{{ $file['name'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold text-slate-800">Archivos cargados</h3>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
                        {{ count($existingFiles) }} registrados
                    </span>
                </div>

                <div class="space-y-2">
                    @forelse ($existingFiles as $file)
                        <div class="flex flex-col gap-3 rounded-xl bg-white p-3 ring-1 ring-slate-200 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <a href="{{ Storage::url($file->ruta_archivo) }}" target="_blank" class="truncate text-sm font-medium text-blue-600 hover:underline">
                                    {{ $file->nombre_archivo }}
                                </a>
                                <div class="mt-1 flex flex-wrap gap-2 text-xs text-slate-500">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1">{{ $file->tipo_archivo ?? 'Archivo' }}</span>
                                    <span class="rounded-full {{ !$file->flag_descarga ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }} px-2.5 py-1">
                                        {{ !$file->flag_descarga ? 'Pendiente de descarga' : 'Descargado' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if (!$file->flag_descarga)
                                    <button type="button" wire:click.prevent="downloadFile({{ $file->id }})" class="inline-flex items-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                        Descargar
                                    </button>
                                @endif

                                @can('eliminar-archivo-en-pre-proyecto')
                                    <button type="button" wire:click="deleteFile({{ $file->id }})" class="inline-flex items-center rounded-xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-100" @disabled($modoLectura)>
                                        Eliminar
                                    </button>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl bg-white p-3 text-sm text-slate-500 ring-1 ring-slate-200">
                            No hay archivos cargados para este preproyecto.
                        </div>
                    @endforelse
                </div>

                @error('archivosPendientes')
                    <span class="mt-3 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Direcciones y fechas</h3>
                    <p class="text-xs text-slate-500">Configura facturacion, entrega, tipo de envio y fechas clave del proyecto.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                    Planeacion
                </span>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                    <label class="block text-sm font-medium text-slate-700">Direccion Fiscal</label>
                    <select wire:model="direccion_fiscal_id" class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" @disabled($modoLectura)>
                        <option value="">Seleccionar Direccion Fiscal</option>
                        @foreach ($direccionesFiscales as $direccion)
                            <option value="{{ $direccion->id }}">
                                {{ $direccion->rfc ?? '—' }} — {{ $direccion->calle }}
                            </option>
                        @endforeach
                    </select>
                    @error('direccion_fiscal_id') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                    <label class="block text-sm font-medium text-slate-700">Direccion de Entrega</label>
                    <select wire:change="cargarTiposEnvio" wire:model="direccion_entrega_id" class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" @disabled($modoLectura)>
                        <option value="">Selecciona una direccion</option>
                        @foreach ($direccionesEntrega as $direccion)
                            <option value="{{ $direccion->id }}">
                                {{ $direccion->nombre_contacto ?? '—' }} — {{ $direccion->calle }}
                            </option>
                        @endforeach
                    </select>
                    @error('direccion_entrega_id') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                    <label class="block text-sm font-medium text-slate-700">Tipo de Envio</label>
                    <select wire:change="on_Calcula_Fechas_Entrega" wire:model="id_tipo_envio" class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" @disabled($modoLectura || !$direccion_entrega_id)>
                        <option value="">Selecciona un tipo de envio</option>
                        @foreach ($tiposEnvio as $tipo)
                            <option value="{{ $tipo->id }}">
                                {{ $tipo->nombre }}{{ isset($tipo->dias_envio) ? ' (' . $tipo->dias_envio . ' dias)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_tipo_envio') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
                @can('edit-pre-project-fecha-produccion')
                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                        <label class="block text-sm font-medium text-slate-700">Fecha Produccion</label>
                        <input type="date" wire:model="fecha_produccion" class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm" readonly>
                    </div>
                @endcan

                @can('edit-pre-project-fecha-embarque')
                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                        <label class="block text-sm font-medium text-slate-700">Fecha Embarque</label>
                        <input type="date" wire:model="fecha_embarque" class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm" readonly>
                    </div>
                @endcan

                @can('edit-pre-project-fecha-entrega')
                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                        <label class="block text-sm font-medium text-slate-700">Fecha Entrega</label>
                        <input wire:change="validarFechaEntrega" wire:model="fecha_entrega" type="date" class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" min="{{ date('Y-m-d') }}" id="fechaEntrega" @readonly($modoLectura)>
                    </div>
                @endcan
            </div>

            @error('error')
                <span class="mt-3 block text-sm text-red-600">{{ $message }}</span>
            @enderror

            @if ($mensaje_produccion)
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    {{ $mensaje_produccion }}
                </div>
            @endif
        </div>

        <div class="flex flex-wrap gap-3">
            @can('edit-pre-project--boton-guardar-cambios')
                <button type="submit" class="inline-flex items-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Guardar Cambios
                </button>
            @endcan

            @can('edit-pre-project--Pre-Aprobar-proyecto')
                <button type="button" wire:click="preAprobarProyecto" class="inline-flex items-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    Pre aprobar proyecto
                </button>
            @endcan
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Livewire.on('setReadOnlyMode', () => {
                setTimeout(function () {
                    $("input, textarea").attr("readonly", "readonly");
                    $("select, button").attr("disabled", "disabled");
                }, 100);
            });

            Livewire.on('redirect', (url) => { window.location.href = url; });
        });
    </script>
    @endpush
</div>
