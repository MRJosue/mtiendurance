<div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Gestión de Layouts</h2>

    <!-- Formulario de Layout -->
    <form wire:submit.prevent="{{ $modoEdicion ? 'guardarElementos' : 'crear' }}" class="mb-6 space-y-4">
        <div>
            <label class="block">Nombre</label>
            <input type="text" wire:model="nombre" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block">Descripción</label>
            <textarea wire:model="descripcion" class="w-full border rounded p-2"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block">Producto</label>
                <select wire:model="producto_id" class="w-full border rounded p-2">
                    <option value="">-- Seleccionar --</option>
                    @foreach($productos as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block">Categoría</label>
                <select wire:model="categoria_id" class="w-full border rounded p-2">
                    <option value="">-- Seleccionar --</option>
                    @foreach($categorias as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($modoEdicion)
            <div class="bg-gray-50 p-4 rounded border">
                <h3 class="text-lg font-semibold mb-2">Elementos del Layout</h3>

                @foreach ($elementos as $i => $el)
                    <div class="grid grid-cols-10 gap-2 items-center mb-2">
                        <button 
                            type="button"
                            x-data
                            x-on:click="$dispatch('seleccionar-elemento', { index: {{ $i }} })"
                            class="bg-blue-600 text-white text-xs px-2 py-1 rounded hover:bg-blue-700">
                            Seleccionar
                        </button>

                        <input 
                            type="text"
                            maxlength="5"
                            placeholder="Letra"
                            wire:model.lazy="elementos.{{ $i }}.letra"
                            class="border rounded p-1 uppercase"
                        >

                        <select wire:model="elementos.{{ $i }}.tipo" class="border rounded p-1">
                            <option value="texto">Texto</option>
                            <option value="imagen">Imagen</option>
                            <option value="caracteristica">Característica</option>
                        </select>



                        <select wire:model="elementos.{{ $i }}.caracteristica_id" class="border rounded p-1">
                            <option value="">-</option>
                            @foreach ($caracteristicas as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>

                        <input type="number" placeholder="X" wire:model="elementos.{{ $i }}.posicion_x" class="border rounded p-1" step="1" >
                        <input type="number" placeholder="Y" wire:model="elementos.{{ $i }}.posicion_y" class="border rounded p-1" step="1" >
                        <input type="number" placeholder="Ancho" wire:model="elementos.{{ $i }}.ancho" class="border rounded p-1" step="1" >
                        <input type="number" placeholder="Alto" wire:model="elementos.{{ $i }}.alto" class="border rounded p-1" step="1" >
                        <input type="number" placeholder="Orden" min="0" wire:model="elementos.{{ $i }}.orden" class="border rounded p-1" step="1" />
                        <div class="flex space-x-1">
                            <button type="button"
                                class="bg-gray-300 px-2 rounded hover:bg-gray-400"
                                wire:click="cambiarOrden({{ $i }}, -1)">
                                ↑
                            </button>
                            <button type="button"
                                class="bg-gray-300 px-2 rounded hover:bg-gray-400"
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
                                class="border rounded p-1 text-sm"
                            />
                            @endif
                        </div>
                    </div>

                    <pre x-text="JSON.stringify(el)"></pre>

                @endforeach

                <button type="button" wire:click="addElemento"
                    class="mt-2 bg-gray-700 text-white px-3 py-1 rounded hover:bg-gray-800">
                    + Añadir Elemento
                </button>

                <div 
                    x-data="layoutEditor(@entangle('elementos'))" 
                    x-init="init()"
                    class="relative border border-gray-300 bg-gray-100 h-[500px] w-full mb-6 overflow-hidden rounded"
                >
                <template x-for="(el, index) in elementos" :key="index">
                    <div 
                        x-bind:style="`left: ${el.posicion_x}px; top: ${el.posicion_y}px; width: ${el.ancho}px; height: ${el.alto}px; z-index: ${index === selectedIndex ? 50 : 10};`"
                        x-on:mousedown="startDrag($event, index)"
                        x-on:click.stop="selectedIndex = index"
                        class="absolute bg-blue-500 bg-opacity-30 border border-blue-800 text-xs text-blue-900 font-bold flex items-center justify-center cursor-move rounded shadow group transition-all duration-150 overflow-hidden"
                        :class="{'ring-2 ring-blue-700': index === selectedIndex}"
                    >
                
                        <!-- Imagen de fondo (si hay) -->
                        <template x-if="el.tipo === 'imagen' && el.configuracion?.url">
                            <img 
                                :src="`/${el.configuracion.url}`"
                                class="absolute inset-0 w-full h-full object-contain z-0 pointer-events-none"
                                alt="Imagen del layout"
                            />
                        </template>
                
                        <!-- Capa superior: letra -->
                        <span x-show="el.letra" x-text="el.letra" class="absolute top-0 left-1 text-xs text-black font-bold z-20"></span>
                
                        <!-- Tipo en texto (excepto imagen) -->
                        <template x-if="el.tipo !== 'imagen' || !el.configuracion?.url">
                            <span x-text="el.tipo.toUpperCase()" class="z-10 pointer-events-none"></span>
                        </template>
                
                        <!-- Botón eliminar -->
                        <button 
                            x-show="index === selectedIndex"
                            x-on:click.stop="deleteSelected"
                            class="absolute top-0 right-0 bg-red-600 text-white text-xs px-1 py-0.5 rounded-bl shadow hover:bg-red-700 z-30">
                            ✕
                        </button>
                    </div>
                </template>
                </div>
            </div>


            
        @endif

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            {{ $modoEdicion ? 'Guardar Layout' : 'Crear Layout' }}
        </button>
    </form>

    <!-- Lista de Layouts -->
    <div class="border-t pt-4">
        <h3 class="text-lg font-bold mb-2">Tus Layouts</h3>
        <ul class="space-y-2">
            @foreach ($layouts as $layout)
                <li class="p-2 bg-white rounded shadow flex justify-between items-center">
                    <span>{{ $layout->nombre }}</span>
                    <button wire:click="editar({{ $layout->id }})" class="text-blue-600 hover:underline">Editar</button>
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
