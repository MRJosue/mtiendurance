<div x-data class="container mx-auto p-6">
    <h2 class="text-xl font-bold mb-4">Crear Nuevo Usuario</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="createUser" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700">Nombre</label>
                <input type="text" wire:model.live="name" class="w-full p-2 border rounded" autocomplete="off">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-gray-700">Correo Electrónico</label>
                <input type="email" wire:model.live="email" class="w-full p-2 border rounded" autocomplete="off">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-gray-700">Contraseña</label>
                <input type="password" wire:model.live="password" class="w-full p-2 border rounded" autocomplete="new-password">
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-gray-700">Confirmar Contraseña</label>
                <input type="password" wire:model.live="password_confirmation" class="w-full p-2 border rounded" autocomplete="new-password">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-gray-700">Rol</label>
                <select wire:model.live="role" class="w-full p-2 border rounded">
                    <option value="">Seleccione un rol</option>
                    @foreach($rolesDisponibles as $rol)
                        <option value="{{ $rol }}">{{ ucfirst($rol) }}</option>
                    @endforeach
                </select>
                @error('role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- CLIENTE PRINCIPAL: captura Empresa y Sucursal nuevas --}}
        @if($role === 'cliente_principal')
            <div class="rounded-lg border p-4 bg-gray-50">
                <h3 class="font-semibold mb-2">Datos de Empresa y Sucursal (nuevo cliente principal)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700">Nombre de la Empresa</label>
                        <input type="text" wire:model.live="empresa_nombre" class="w-full p-2 border rounded" placeholder="Ej. Industrias ABC">
                        @error('empresa_nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700">RFC (opcional)</label>
                        <input type="text" wire:model.live="empresa_rfc" class="w-full p-2 border rounded">
                        @error('empresa_rfc') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700">Teléfono (opcional)</label>
                        <input type="text" wire:model.live="empresa_telefono" class="w-full p-2 border rounded">
                        @error('empresa_telefono') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700">Dirección (opcional)</label>
                        <input type="text" wire:model.live="empresa_direccion" class="w-full p-2 border rounded">
                        @error('empresa_direccion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700">Nombre de Sucursal Principal</label>
                        <input type="text" wire:model.live="sucursal_nombre" class="w-full p-2 border rounded" placeholder="Matriz">
                        @error('sucursal_nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700">Teléfono Sucursal (opcional)</label>
                        <input type="text" wire:model.live="sucursal_telefono" class="w-full p-2 border rounded">
                        @error('sucursal_telefono') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-gray-700">Dirección Sucursal (opcional)</label>
                        <input type="text" wire:model.live="sucursal_direccion" class="w-full p-2 border rounded">
                        @error('sucursal_direccion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        @endif

        {{-- CLIENTE SUBORDINADO: seleccionar Empresa (con búsqueda tipo “usuario”) y Sucursal existentes --}}
        @if($role === 'cliente_subordinado')
            <div class="rounded-lg border p-4 bg-gray-50">
                <h3 class="font-semibold mb-2">Asignación a Empresa y Sucursal existentes</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- EMPRESA - BÚSQUEDA estilo sugerido --}}
                    <div 
                        x-data="{
                            open: false,
                            search: @entangle('empresaQuery').live,
                            selectedId: @entangle('empresa_id_sub'),
                            puedeBuscar: @js($puedeBuscarEmpresas),
                            get hasResults(){ return (this.$wire.empresasSugeridas || []).length > 0 },
                            select(id){
                                this.selectedId = id;
                                const e = (this.$wire.empresasSugeridas || []).find(x => x.id === id);
                                this.search = e ? (e.nombre + (e.rfc ? ' ('+e.rfc+')' : '')) : '';
                                this.open = false;
                            },
                            init(){
                                if(this.selectedId){
                                    const e = (this.$wire.empresasSugeridas || []).find(x => x.id === this.selectedId);
                                    if(e){
                                        this.search = e.nombre + (e.rfc ? ' ('+e.rfc+')' : '');
                                    }
                                }
                            }
                        }"
                    >
                        <label class="block mb-1 font-medium text-gray-700">Seleccionar Empresa</label>

                        <input
                            x-model="search"
                            @focus="open = puedeBuscar"
                            @click.outside="open = false"
                            placeholder="Buscar por nombre o RFC…"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            :readonly="!puedeBuscar"
                            :class="{'bg-gray-100 text-gray-500 cursor-not-allowed': !puedeBuscar}"
                            type="text"
                        />

                        <div x-show="open && puedeBuscar" x-transition class="relative">
                            <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg mt-1 max-h-56 overflow-y-auto shadow">
                                <template x-for="e in ($wire.empresasSugeridas || [])" :key="e.id">
                                    <div
                                        @click="select(e.id)"
                                        class="px-3 py-2 cursor-pointer hover:bg-blue-100 text-sm flex items-center justify-between"
                                        :class="{'bg-blue-50': e.id === selectedId}"
                                    >
                                        <div class="truncate">
                                            <span class="font-medium" x-text="e.nombre"></span>
                                            <span class="text-gray-500 text-xs ml-1" x-text="e.rfc ? '('+e.rfc+')' : ''"></span>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="!hasResults" class="px-3 py-2 text-gray-500 text-sm italic">
                                    No se encontraron empresas
                                </div>
                            </div>
                        </div>

                        {{-- Píldora del seleccionado --}}
                        <div class="mt-2" x-show="selectedId">
                            <span class="inline-flex items-center gap-2 text-sm bg-blue-50 text-blue-700 px-3 py-1 rounded-full">
                                Empresa seleccionada:
                                <span
                                    x-text="(() => {
                                        const lista = ($wire.empresasSugeridas || []);
                                        const e = lista.find(x => x.id === selectedId);
                                        return e ? `${e.nombre}${e.rfc ? ' ('+e.rfc+')' : ''}` : (search || `ID ${selectedId}`);
                                    })()"
                                ></span>
                            </span>
                        </div>

                        @error('empresa_id_sub') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- SUCURSAL dependiente de la empresa seleccionada --}}
                    <div>
                        <label class="block text-gray-700">Sucursal</label>
                        <select wire:model.live="sucursal_id_sub" class="w-full p-2 border rounded" @disabled(!$empresa_id_sub)>
                            <option value="">Seleccione sucursal...</option>
                            @foreach($sucursalesDeEmpresa as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->nombre }} ({{ (int)$s->tipo === 1 ? 'Principal' : 'Secundaria' }})
                                </option>
                            @endforeach
                        </select>
                        @error('sucursal_id_sub') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        @endif

        {{-- STAFF: seleccionar Empresa y Sucursal (por defecto MTIENDURANCE + sucursal principal) --}}
        @if($tipo === 3)
            <div class="rounded-lg border p-4 bg-gray-50">
                <h3 class="font-semibold mb-2">Asignación de Empresa y Sucursal (Staff)</h3>
                <p class="text-xs text-gray-500 mb-3">
                    Por defecto se selecciona la empresa <span class="font-semibold">MTIENDURANCE</span> y su sucursal principal si existen.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700">Empresa</label>
                        <select wire:model.live="empresa_id_staff" class="w-full p-2 border rounded">
                            <option value="">Seleccione empresa...</option>
                            @foreach($empresasStaff as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                            @endforeach
                        </select>
                        @error('empresa_id_staff') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700">Sucursal</label>
                        <select
                            wire:model.live="sucursal_id_staff"
                            class="w-full p-2 border rounded"
                            @disabled(!$empresa_id_staff)
                        >
                            <option value="">Seleccione sucursal...</option>
                            @foreach($sucursalesStaff as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->nombre }} ({{ (int)$s->tipo === 1 ? 'Principal' : 'Secundaria' }})
                                </option>
                            @endforeach
                        </select>
                        @error('sucursal_id_staff') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        @endif

        <button type="submit" class="w-full sm:w-auto bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
            Crear Usuario
        </button>
    </form>
</div>

{{-- Scripts encapsulados --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    window.addEventListener('notify', (e) => {
        const msg = e.detail?.message || 'Acción realizada';
        const toast = document.createElement('div');
        toast.textContent = msg;
        toast.className = 'fixed top-4 right-4 bg-emerald-600 text-white px-4 py-2 rounded-lg shadow z-[100]';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    });
});
</script>
