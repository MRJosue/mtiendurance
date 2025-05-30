<div
    x-data="{
        open: false,
        search: '',
        selected: @entangle($entangle).live || @js($seleccionados),
        opciones: @js($opciones),
        filtrar() {
            return this.opciones.filter(u => u.name.toLowerCase().includes(this.search.toLowerCase()));
        },
        toggle(id) {
            if (!Array.isArray(this.selected)) this.selected = [];
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(i => i !== id);
            } else {
                this.selected.push(id);
            }
        },
        isChecked(id) {
            return Array.isArray(this.selected) && this.selected.includes(id);
        }
    }"
    class="relative w-full max-w-xl"
>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    <div @click="open = !open" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white cursor-pointer">
        <template x-if="Array.isArray(selected) && selected.length > 0 && selected.some(i => !!i)">
            <div class="flex flex-wrap gap-2">
                <template x-for="id in selected" :key="id">
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs" x-text="opciones.find(o => o.id === id)?.name"></span>
                </template>
            </div>
        </template>
        <template x-if="!Array.isArray(selected) || selected.length === 0 || selected.every(i => !i)">
            <span class="text-gray-400 text-sm">Selecciona una o más opciones...</span>
        </template>
    </div>

    <!-- Dropdown -->
    <div x-show="open" @click.away="open = false" class="absolute mt-1 z-50 w-full bg-white border border-gray-300 rounded-lg shadow max-h-60 overflow-y-auto">
        <!-- Buscador + seleccionar todo -->
        <div class="flex items-center gap-2 px-4 py-2 border-b border-gray-200">
            <input
                type="text"
                x-model="search"
                placeholder="Buscar..."
                class="w-full px-2 py-1 border border-gray-200 rounded focus:outline-none text-sm"
            >
            <label class="flex items-center space-x-1 text-sm">
                    <input
                        type="checkbox"
                        @change="() => {
                            if (!Array.isArray(selected)) selected = [];

                            const visibles = filtrar().map(u => u.id);
                            const todosSeleccionados = visibles.length > 0 && visibles.every(id => selected.includes(id));

                            if (todosSeleccionados) {
                                selected = selected.filter(id => !visibles.includes(id));
                            } else {
                                visibles.forEach(id => {
                                    if (!selected.includes(id)) selected.push(id);
                                });
                            }
                        }"
                        :checked="Array.isArray(selected) && filtrar().length > 0 && filtrar().every(u => selected.includes(u.id))"
                    />

                <span>Todas</span>
            </label>
        </div>

        <!-- Lista de usuarios -->
        <template x-for="user in filtrar()" :key="user.id">
            <label class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer">
                <input type="checkbox" :value="user.id" :checked="isChecked(user.id)" @change="toggle(user.id)" class="mr-2">
                <span x-text="user.name"></span>
            </label>
        </template>
    </div>
</div>
