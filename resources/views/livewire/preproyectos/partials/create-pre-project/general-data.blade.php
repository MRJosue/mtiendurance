<div
    x-data="{
        open: false,
        search: @entangle('usuarioQuery').live,
        selectedId: @entangle('selectedUserLookupId'),
        puedeBuscar: @js($puedeBuscarUsuarios),
        get hasResults(){ return (this.$wire.usuariosSugeridos || []).length > 0 },
        select(id){
            this.selectedId = id;
            const user = (this.$wire.usuariosSugeridos || []).find(u => u.id === id);
            this.search = user ? user.name + ' (' + user.email + ')' : '';
            this.open = false;
        },
        init(){
            const authIsCliente = @js(auth()->user()?->roles()->where('tipo', 1)->exists() ?? false);
            if(!this.selectedId){
                if(authIsCliente){
                    this.selectedId = {{ auth()->id() }};
                    this.search = '{{ auth()->user()->name }} ({{ auth()->user()->email }})';
                } else {
                    this.selectedId = null;
                    this.search = '';
                }
            } else {
                const user = (this.$wire.usuariosSugeridos || []).find(u => u.id === this.selectedId);
                if(user){
                    this.search = user.name + ' (' + user.email + ')';
                }
            }
        }
    }"
    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/80"
>
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Datos generales del preproyecto</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Define al cliente, la información base y el producto a desarrollar.</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
            Paso inicial
        </span>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="xl:col-span-2">
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Seleccionar Usuario</label>
            <input
                x-model="search"
                @focus="open = puedeBuscar"
                @click.outside="open = false"
                placeholder="Buscar por nombre o email…"
                class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                :readonly="!puedeBuscar"
                :class="{'bg-slate-100 text-slate-500 cursor-not-allowed dark:bg-slate-800 dark:text-slate-400': !puedeBuscar}"
                type="text"
            />

            <div x-show="open && puedeBuscar" x-transition class="relative">
                <div class="absolute z-50 mt-1 max-h-56 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900">
                    <template x-for="user in ($wire.usuariosSugeridos || [])" :key="user.id">
                        <div
                            @click="select(user.id)"
                            class="flex cursor-pointer items-center justify-between px-3 py-2 text-sm text-slate-700 hover:bg-blue-100 dark:text-slate-100 dark:hover:bg-slate-800"
                            :class="{'bg-blue-50 dark:bg-slate-800': user.id === selectedId}"
                        >
                            <div class="truncate">
                                <span class="font-medium" x-text="user.name"></span>
                                <span class="ml-1 text-xs text-slate-500 dark:text-slate-400" x-text="'(' + user.email + ')' "></span>
                            </div>
                            <template x-if="user.id === {{ auth()->id() }}">
                                <span class="ml-2 shrink-0 text-xs text-blue-500 dark:text-blue-400">— Actual</span>
                            </template>
                        </div>
                    </template>

                    <div x-show="!hasResults" class="px-3 py-2 text-sm italic text-slate-500 dark:text-slate-400">
                        No se encontraron usuarios
                    </div>
                </div>
            </div>

            <div class="mt-2" x-show="selectedId">
                <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-sm text-blue-700 dark:bg-blue-900/30 dark:text-blue-200">
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
            @error('selectedUserId')
                <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nombre</label>
            <input type="text" wire:model="nombre" class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
            @error('nombre') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Categoría</label>
            <select wire:change="onCategoriaChange" wire:model="categoria_id" class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                <option value="">Seleccionar Categoría</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                @endforeach
            </select>
            @error('categoria_id') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="xl:col-span-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Descripción</label>
            <textarea wire:model="descripcion" rows="3" class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"></textarea>
            @error('descripcion') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Producto</label>
            <select wire:change="onProductoChange" wire:model="producto_id" class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
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
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">¿El proyecto será armado?</label>
                    <select wire:model="seleccion_armado" wire:change="despligaformopciones" class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                        <option value="">Seleccionar</option>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                    @error('seleccion_armado') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
            @endif
        </div>
    </div>
</div>
