<div
    x-data="{
        open: false,
        search: '',
        selected: @entangle($entangle).live || {{ $seleccionado ?? 'null' }},
        opciones: @js($opciones),
        filtrar() {
            return this.opciones.filter(u => u.name.toLowerCase().includes(this.search.toLowerCase()));
        },
        seleccionar(id) {
            this.selected = id;
            $dispatch('change'); // <--- EMITE EVENTO PARA wire:change
            this.open = false;
        },
        getNombre() {
            const seleccionado = this.opciones.find(o => o.id === this.selected);
            return seleccionado ? seleccionado.name : 'Selecciona una opción...';
        }
    }"
    x-on:change.window="$wire.{{ $onchange }}(selected)" {{-- Livewire ejecuta función --}}
    class="relative w-full max-w-xl"
>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>

    <div @click="open = !open" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white cursor-pointer text-sm text-gray-700">
        <span x-text="getNombre()"></span>
    </div>

    <!-- Dropdown -->
    <div x-show="open" @click.away="open = false" class="absolute mt-1 z-50 w-full bg-white border border-gray-300 rounded-lg shadow max-h-60 overflow-y-auto">
        <!-- Buscador -->
        <div class="px-4 py-2 border-b border-gray-200">
            <input
                type="text"
                x-model="search"
                placeholder="Buscar..."
                class="w-full px-2 py-1 border border-gray-200 rounded focus:outline-none text-sm"
            >
        </div>

        <!-- Opciones -->
        <template x-for="user in filtrar()" :key="user.id">
            <div @click="seleccionar(user.id)" class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm" x-text="user.name"></div>
        </template>
    </div>
</div>
