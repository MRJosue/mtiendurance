<div  class="container mx-auto p-6 text-gray-900 dark:text-gray-100">
    <!-- Botones de acción -->
    <div class="mb-4 flex flex-wrap space-y-2 sm:space-y-0 sm:space-x-4">
        <button
            class="w-full sm:w-auto rounded-lg bg-green-500 px-4 py-2 text-white transition hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-500"
            wire:click="abrirModal"
        >
            Nuevo Flujo
        </button>
        <button
            class="w-full sm:w-auto rounded-lg bg-red-500 px-4 py-2 text-white transition hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-red-600 dark:hover:bg-red-500"
            :disabled="selectedFlujos.length === 0"
            wire:click="deleteSelected"
        >
            Eliminar Seleccionados
        </button>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <table class="min-w-full border-collapse rounded-lg">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">
                        <input
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                            wire:model="selectAll"
                            @change="selectedFlujos = $event.target.checked ? @js($flujos->pluck('id')) : []"
                        />
                    </th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">ID</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Nombre</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Descripción</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($flujos as $flujo)
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/70">
                        <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">
                            <input
                                type="checkbox"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                wire:model="selectedFlujos"
                                value="{{ $flujo->id }}"
                            />
                        </td>
                        <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $flujo->id }}</td>
                        <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $flujo->nombre }}</td>
                        <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $flujo->descripcion ?? '—' }}</td>
                        <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">
                            <button
                                class="text-blue-600 hover:underline dark:text-blue-400"
                                wire:click="abrirModal({{ $flujo->id }})"
                            >
                                Editar
                            </button>
                            <button
                                class="ml-2 text-red-600 hover:underline dark:text-red-400"
                                wire:click="delete({{ $flujo->id }})"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->

    <!-- Modal -->
    @if ($modalOpen)
    <div 
        x-data="{ selectedFlujos: @entangle('selectedFlujos'), modalOpen: @entangle('modalOpen') }" 
        x-show="modalOpen" 
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
        @keydown.escape.window="modalOpen = false"
    >
        <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900 dark:ring-1 dark:ring-white/10" @click.away="modalOpen = false">
            <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-gray-100">
                {{ $editMode ? 'Editar Flujo' : 'Nuevo Flujo' }}
            </h2>
            <form wire:submit.prevent="guardar">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nombre</label>
                    <input type="text" wire:model.defer="nombre" class="mt-1 w-full rounded-lg border border-gray-300 bg-white p-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-blue-400 dark:focus:ring-blue-400" />
                    @error('nombre') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Descripción</label>
                    <textarea wire:model.defer="descripcion" class="mt-1 w-full rounded-lg border border-gray-300 bg-white p-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-blue-400 dark:focus:ring-blue-400"></textarea>
                    @error('descripcion') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>

                <div
                    x-data="flujoEditor()"
                    x-init="init(@js($config));
                        $watch('steps', () => {
                            $refs.configTextarea.value = JSON.stringify({ steps }, null, 2);
                            $refs.configTextarea.dispatchEvent(new Event('input'));
                        })"
                    class="mb-4 max-h-[400px] overflow-auto rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/60"
                >
                    <h3 class="mb-2 font-semibold text-gray-900 dark:text-gray-100">Configuración de Pasos (steps)</h3>

                    <template x-for="(step, index) in steps" :key="index">
                        <div class="mb-3 rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-900">


                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-2">
                                <strong class="text-sm text-gray-900 dark:text-gray-100 sm:text-base" x-text="'Paso ' + (index + 1) + ': ' + (step.name || '—')"></strong>

                                <div class="flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="rounded bg-gray-200 px-2 py-1 text-xs text-gray-700 transition hover:bg-gray-300 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                        @click="moveStepUp(index)"
                                        :disabled="index === 0"
                                        title="Subir"
                                    >
                                        ▲ Subir
                                    </button>

                                    <button
                                        type="button"
                                        class="rounded bg-gray-200 px-2 py-1 text-xs text-gray-700 transition hover:bg-gray-300 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                        @click="moveStepDown(index)"
                                        :disabled="index === steps.length - 1"
                                        title="Bajar"
                                    >
                                        ▼ Bajar
                                    </button>

                                    <button
                                        type="button"
                                        class="rounded bg-red-500 px-2 py-1 text-xs text-white transition hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500"
                                        @click="removeStep(index)"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </div>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nombre (name)</label>
                                    <select x-model="step.name" class="w-full rounded border border-gray-300 bg-white p-1 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                        <option value="" disabled>-- Selecciona un nombre --</option>
                                        <template x-for="option in opciones" :key="option">
                                            <option :value="option" x-text="option"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Grupo</label>
                                    <input type="number" min="1" x-model.number="step.grupo" class="w-full rounded border border-gray-300 bg-white p-1 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Descripción</label>
                                    <textarea x-model="step.descripcion" class="w-full rounded border border-gray-300 bg-white p-1 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400"></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Siguientes pasos (next) — separa por comas</label>
                                    <input 
                                        type="text" 
                                        x-model="step.nextText" 
                                        @input="step.next = step.nextText.split(',').map(s => s.trim()).filter(s => s.length > 0)" 
                                        class="w-full rounded border border-gray-300 bg-white p-1 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" 
                                    />
                                </div>
                            </div>
                        </div>
                    </template>

                    <button 
                        type="button" 
                        @click="addStep()" 
                        class="rounded bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500"
                    >
                        + Agregar Paso
                    </button>
                </div>

                <!-- Mantener sincronizado el textarea JSON oculto para Livewire -->
                <textarea x-ref="configTextarea" wire:model.defer="config" class="hidden"></textarea>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="modalOpen = false" class="rounded bg-gray-300 px-4 py-2 text-gray-800 transition hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.flujoEditor = () => ({
                    steps: [],
                    opciones: [
                        'POR APROBAR',
                        'POR PROGRAMAR',
                        'PROGRAMADO',
                        'IMPRESIÓN',
                        'CORTE',
                        'COSTURA',
                        'ENTREGA',
                        'FACTURACIÓN',
                        'COMPLETADO',
                        'RECHAZADO',
                    ],

                    // Normaliza para comparar sin importar mayúsculas/acentos
                    normalize(str) {
                        return (str ?? '')
                            .toString()
                            .trim()
                            .normalize('NFD')
                            .replace(/\p{Diacritic}/gu, '')
                            .toUpperCase();
                    },

                    init(initialJson = '{}') {
                        try {
                            const obj = typeof initialJson === 'string' ? JSON.parse(initialJson) : initialJson;

                            const opcionesNorm = this.opciones.map(o => this.normalize(o));

                            this.steps = (obj.steps || []).map(step => {
                                const nombreNorm = this.normalize(step.name ?? '');
                                const matchIndex = opcionesNorm.indexOf(nombreNorm);

                                return {
                                    ...step,
                                    name: matchIndex !== -1 ? this.opciones[matchIndex] : '',
                                    grupo: Number(step.grupo ?? 1),
                                    descripcion: step.descripcion ?? '',
                                    next: Array.isArray(step.next) ? step.next : [],
                                    nextText: (Array.isArray(step.next) ? step.next : []).join(', '),
                                };
                            });
                        } catch (e) {
                            this.steps = [];
                        }
                    },

                    addStep() {
                        this.steps.push({
                            name: '',
                            grupo: 1,
                            descripcion: '',
                            next: [],
                            nextText: '',
                        });
                    },

                    removeStep(index) {
                        this.steps.splice(index, 1);
                    },

                    moveStepUp(index) {
                        if (index <= 0) return;
                        const tmp = this.steps[index - 1];
                        this.steps[index - 1] = this.steps[index];
                        this.steps[index] = tmp;
                    },

                    moveStepDown(index) {
                        if (index >= this.steps.length - 1) return;
                        const tmp = this.steps[index + 1];
                        this.steps[index + 1] = this.steps[index];
                        this.steps[index] = tmp;
                    },
                });
            });
    </script>
</div>
