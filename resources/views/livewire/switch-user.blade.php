<div
    x-data="{
        search: '',
        open: false,
        selectedId: @entangle('userId'),
        users: @js($users),
        get filtered() {
            if (this.search === '') return this.users;
            return this.users.filter(u =>
                u.name.toLowerCase().includes(this.search.toLowerCase()) ||
                u.email.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        select(id) {
            this.selectedId = id;
            this.open = false;
        }
    }"
    class="p-4 bg-white rounded-lg shadow max-w-md mx-auto"
>
    <form wire:submit.prevent="switchUser">
        <div class="mb-4 relative">
            <label class="block mb-1 font-medium text-gray-700">Seleccionar Usuario</label>

            <!-- Campo de búsqueda -->
            <input
                x-model="search"
                @focus="open = true"
                @click.outside="open = false"
                placeholder="Buscar usuario..."
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                type="text"
            />

            <!-- Dropdown con resultados -->
            <div
                x-show="open"
                x-transition
                class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg mt-1 max-h-56 overflow-y-auto shadow"
            >
                <template x-for="user in filtered" :key="user.id">
                    <div
                        @click="select(user.id)"
                        class="px-3 py-2 cursor-pointer hover:bg-blue-100 text-sm"
                        :class="{'bg-blue-50': user.id === selectedId}"
                    >
                        <span x-text="user.name"></span>
                        <span class="text-gray-500 text-xs" x-text="'(' + user.email + ')'"></span>
                        <template x-if="user.id === {{ auth()->id() }}">
                            <span class="text-blue-500 text-xs ml-1">— Actual</span>
                        </template>
                    </div>
                </template>

                <div
                    x-show="filtered.length === 0"
                    class="px-3 py-2 text-gray-500 text-sm italic"
                >
                    No se encontraron usuarios
                </div>
            </div>
        </div>

        <button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full"
        >
            Cambiar Usuario
        </button>
    </form>
</div>
