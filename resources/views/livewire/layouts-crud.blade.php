<div class="p-6 text-gray-900 dark:text-gray-100">
    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-gray-100">Gestión de Layouts</h2>

    <form wire:submit.prevent="{{ $modoEdicion ? 'guardarElementos' : 'crear' }}" class="mb-6 space-y-4">
        <div>
            <label class="block text-gray-700 dark:text-gray-300">Nombre</label>
            <input type="text" wire:model="nombre" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
        </div>
        <div>
            <label class="block text-gray-700 dark:text-gray-300">Descripción</label>
            <textarea wire:model="descripcion" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 dark:text-gray-300">Producto</label>
                <select wire:model="producto_id" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">-- Seleccionar --</option>
                    @foreach($productos as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 dark:text-gray-300">Categoría</label>
                <select wire:model="categoria_id" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">-- Seleccionar --</option>
                    @foreach($categorias as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($modoEdicion)
            <div class="rounded border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/60">
                <h3 class="mb-2 text-lg font-semibold">Elementos del Layout</h3>

                @foreach ($elementos as $i => $el)
                    <div class="mb-2 grid grid-cols-10 items-center gap-2">
                        <button 
                            type="button"
                            x-data
                            x-on:click="$dispatch('seleccionar-elemento', { index: {{ $i }} })"
                            class="rounded bg-blue-600 px-2 py-1 text-xs text-white hover:bg-blue-700">
                            Seleccionar
                        </button>

                        <input 
                            type="text"
                            maxlength="5"
                            placeholder="Letra"
                            wire:model.lazy="elementos.{{ $i }}.letra"
                            class="rounded border border-gray-300 bg-white p-1 uppercase text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                        >

                        <select wire:model="elementos.{{ $i }}.tipo" class="rounded border border-gray-300 bg-white p-1 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <option value="texto">Texto</option>
                            <option value="imagen">Imagen</option>
                            <option value="caracteristica">Característica</option>
                        </select>

                        <select wire:model="elementos.{{ $i }}.caracteristica_id" class="rounded border border-gray-300 bg-white p-1 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">-</option>
                            @foreach ($caracteristicas as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>

                        <input type="number" placeholder="X" wire:model="elementos.{{ $i }}.posicion_x" class="rounded border border-gray-300 bg-white p-1 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" step="1" >
                        <input type="number" placeholder="Y" wire:model="elementos.{{ $i }}.posicion_y" class="rounded border border-gray-300 bg-white p-1 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" step="1" >
                        <input type="number" placeholder="Ancho" wire:model="elementos.{{ $i }}.ancho" class="rounded border border-gray-300 bg-white p-1 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" step="1" >
                        <input type="number" placeholder="Alto" wire:model="elementos.{{ $i }}.alto" class="rounded border border-gray-300 bg-white p-1 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" step="1" >
                        <input type="number" placeholder="Orden" min="0" wire:model="elementos.{{ $i }}.orden" class="rounded border border-gray-300 bg-white p-1 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" step="1" />
                        <div class="flex space-x-1">
                            <button type="button"
                                class="rounded bg-gray-300 px-2 hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                wire:click="cambiarOrden({{ $i }}, -1)">
                                ↑
                            </button>
                            <button type="button"
                                class="rounded bg-gray-300 px-2 hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                wire:click="cambiarOrden({{ $i }}, 1)">
                                ↓
                            </button>
                        </div>
                        <div class="flex space-x-1">
                            @if($el['tipo'] === 'imagen')
                            <input
                                type="file"
                                wire:model="imagenesTemp.{{ $i }}"
                                wire:change="$dispatch('imagen-subida')"
                                class="rounded border border-gray-300 bg-white p-1 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            />
                            @endif
                        </div>
                    </div>

                    <pre class="rounded bg-gray-100 p-2 text-xs text-gray-700 dark:bg-gray-900 dark:text-gray-300" x-text="JSON.stringify(el)"></pre>

                @endforeach

                <button type="button" wire:click="addElemento"
                    class="mt-2 rounded bg-gray-700 px-3 py-1 text-white hover:bg-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500">
                    + Añadir Elemento
                </button>

                <div 
                    x-data="layoutEditor(@entangle('elementos'))" 
                    x-init="init()"
                    class="relative mb-6 h-[500px] w-full overflow-hidden rounded border border-gray-300 bg-gray-100 dark:border-gray-600 dark:bg-gray-900"
                >
                <template x-for="(el, index) in elementos" :key="index">
                    <div 
                        x-bind:style="`left: ${el.posicion_x}px; top: ${el.posicion_y}px; width: ${el.ancho}px; height: ${el.alto}px; z-index: ${index === selectedIndex ? 50 : 10};`"
                        x-on:mousedown="startDrag($event, index)"
                        x-on:click.stop="selectedIndex = index"
                        class="group absolute flex cursor-move items-center justify-center overflow-hidden rounded border border-blue-800 bg-blue-500 bg-opacity-30 text-xs font-bold text-blue-900 shadow transition-all duration-150"
                        :class="{'ring-2 ring-blue-700': index === selectedIndex}"
                    >
                        <template x-if="el.tipo === 'imagen' && el.configuracion?.url">
                            <img 
                                :src="`/${el.configuracion.url}`"
                                class="absolute inset-0 z-0 h-full w-full object-contain pointer-events-none"
                                alt="Imagen del layout"
                            />
                        </template>
                
                        <span x-show="el.letra" x-text="el.letra" class="absolute left-1 top-0 z-20 text-xs font-bold text-black dark:text-white"></span>
                
                        <template x-if="el.tipo !== 'imagen' || !el.configuracion?.url">
                            <span x-text="el.tipo.toUpperCase()" class="z-10 pointer-events-none"></span>
                        </template>
                
                        <button 
                            x-show="index === selectedIndex"
                            x-on:click.stop="deleteSelected"
                            class="absolute right-0 top-0 z-30 rounded-bl bg-red-600 px-1 py-0.5 text-xs text-white shadow hover:bg-red-700">
                            ✕
                        </button>
                    </div>
                </template>
                </div>
            </div>
        @endif

        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500">
            {{ $modoEdicion ? 'Guardar Layout' : 'Crear Layout' }}
        </button>
    </form>

    <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
        <h3 class="mb-2 text-lg font-bold">Tus Layouts</h3>
        <ul class="space-y-2">
            @foreach ($layouts as $layout)
                <li class="flex items-center justify-between rounded bg-white p-2 shadow dark:bg-gray-800 dark:ring-1 dark:ring-gray-700">
                    <span>{{ $layout->nombre }}</span>
                    <button wire:click="editar({{ $layout->id }})" class="text-blue-600 hover:underline dark:text-blue-400">Editar</button>
                </li>
            @endforeach
        </ul>
    </div>

    <script>
        function layoutEditor(entangleElementos) {
            return {
                elementos: entangleElementos,
                draggingIndex: null,
                selectedIndex: null,
                offsetX: 0,
                offsetY: 0,
                containerRect: null,

                init() {
                    window.addEventListener('seleccionar-elemento', e => {
                        this.selectedIndex = e.detail.index;
                    });
                },

                startDrag(event, index) {
                    event.preventDefault();
                    this.draggingIndex = index;
                    this.selectedIndex = index;

                    const element = event.target.closest('[x-on\\:mousedown]');
                    const container = element?.closest('.relative');
                    if (!container) return;

                    this.containerRect = container.getBoundingClientRect();
                    const elRect = element.getBoundingClientRect();
                    this.offsetX = event.clientX - elRect.left;
                    this.offsetY = event.clientY - elRect.top;

                    const move = (e) => {
                        if (this.draggingIndex !== null && this.containerRect) {
                            this.elementos[this.draggingIndex].posicion_x = e.clientX - this.containerRect.left - this.offsetX;
                            this.elementos[this.draggingIndex].posicion_y = e.clientY - this.containerRect.top - this.offsetY;
                        }
                    };

                    const stop = () => {
                        window.removeEventListener('mousemove', move);
                        window.removeEventListener('mouseup', stop);
                        this.draggingIndex = null;
                    };

                    window.addEventListener('mousemove', move);
                    window.addEventListener('mouseup', stop);
                },

                deleteSelected() {
                    if (this.selectedIndex !== null) {
                        this.elementos.splice(this.selectedIndex, 1);
                        this.selectedIndex = null;
                    }
                }
            }
        }
    </script>
</div>
