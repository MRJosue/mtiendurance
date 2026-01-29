<div class="w-full">
    <div class="bg-white rounded-lg shadow">
        <div class="p-2 sm:p-4 overflow-x-auto overflow-y-visible">
            {{ $this->table }}
        </div>
    </div>


        @if($modalVerInfo && $infoProyecto)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white p-6 rounded shadow-lg w-full max-w-2xl relative overflow-y-auto max-h-[90vh]">
                    <h2 class="text-xl font-bold mb-4">Detalles del Proyecto</h2>
                    <button 
                        wire:click="$set('modalVerInfo', false)" 
                        class="absolute top-3 right-4 text-gray-500 hover:text-red-600 text-2xl leading-none"
                        title="Cerrar"
                    >&times;</button>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-lg"><span class="font-semibold">Cliente:</span> {{ $infoProyecto->user->name ?? 'Sin usuario' }}</p>
                        </div>
                        <div>
                            <p class="text-lg"><span class="font-semibold">Proyecto:</span> {{ $infoProyecto->nombre }} <span class="text-sm font-bold">ID:{{ $infoProyecto->id }}</span></p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-lg"><span class="font-semibold">Descripción:</span> {{ $infoProyecto->descripcion }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-lg font-semibold">Categoría:</p>
                            <p>{{ $infoProyecto->categoria_sel['nombre'] ?? $infoProyecto->categoria->nombre ?? 'Sin categoría' }}</p>
                        </div>
                        <div>
                            <p class="text-lg font-semibold">Producto:</p>
                            <p>{{ $infoProyecto->producto_sel['id'] ?? $infoProyecto->producto->id ?? '' }} {{ $infoProyecto->producto_sel['nombre'] ?? $infoProyecto->producto->nombre ?? 'Sin producto' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-2">
                        @foreach($infoProyecto->caracteristicas_sel ?? [] as $caracteristica)
                            <div class="p-4 border rounded-lg shadow bg-gray-50">
                                <h3 class="text-lg font-semibold">{{ $caracteristica['nombre'] }}</h3>
                                <ul class="mt-2 list-disc list-inside">
                                    @foreach($caracteristica['opciones'] ?? [] as $opcion)
                                        <li><span class="font-medium">{{ $opcion['nombre'] }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const initPgDropdowns = () => {
            // evita duplicar listeners
            if (window.__pgDropdownsReady) return;
            window.__pgDropdownsReady = true;

            document.addEventListener('click', (e) => {
                const allMenus = document.querySelectorAll('.pg-dd-menu');
                const btn = e.target.closest('.pg-dd-btn');

                // click fuera => cerrar todo
                if (!btn) {
                    allMenus.forEach(m => m.classList.add('hidden'));
                    return;
                }

                const wrap = btn.closest('.pg-dd-wrap');
                const menu = wrap ? wrap.querySelector('.pg-dd-menu') : null;

                // cierra todos menos el actual
                allMenus.forEach(m => {
                    if (m !== menu) m.classList.add('hidden');
                });

                if (menu) menu.classList.toggle('hidden');
            });
        };

        initPgDropdowns();

        // cuando Livewire navega (v3) o rehidrata vistas
        document.addEventListener('livewire:navigated', () => {
            // NO reinicies listener (ya existe), solo cierra dropdowns por si quedaron abiertos
            document.querySelectorAll('.pg-dd-menu').forEach(m => m.classList.add('hidden'));
        });
    });
    </script>

</div>



