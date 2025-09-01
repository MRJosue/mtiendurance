<div x-data class="container mx-auto p-6">
    <div class="mb-4 flex flex-wrap gap-2">
        <input class="w-full sm:w-72 rounded-lg border-gray-300 focus:ring-blue-500"
               placeholder="Buscar hoja…" wire:model.live.debounce.300ms="search">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg"
                @click="$wire.openCreate()">Nueva hoja</button>

        <!-- botón para abrir modal de Filtros y crear en línea -->
        <button class="px-4 py-2 bg-emerald-600 text-white rounded-lg"
                x-on:click="$wire.dispatch('abrir-modal-filtro')">Crear filtro</button>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Nombre</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Rol</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600"># Filtros</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Visible</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Orden</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hojas as $h)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 text-sm">
                        <a href="{{ route('produccion.hojas.show', $h->slug) }}" class="text-blue-600 hover:underline">
                            {{ $h->nombre }}
                        </a>
                    </td>
                    <td class="px-3 py-2 text-sm">{{ $h->rol->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-sm">{{ $h->filtros_count }}</td>
                    <td class="px-3 py-2 text-sm">{{ $h->visible ? 'Sí' : 'No' }}</td>
                    <td class="px-3 py-2 text-sm">{{ $h->orden ?? '—' }}</td>
                    <td class="px-3 py-2 text-sm">
                        <button class="text-blue-600 hover:underline" wire:click="openEdit({{ $h->id }})">Editar</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $hojas->links() }}</div>

    {{-- Modal crear/editar (omito detalle del formulario por espacio: incluye nombre/slug/rol/estados/base_columnas/filtros) --}}
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    window.addEventListener('hojas-notify', e => console.log(e.detail?.message));
});
</script>

