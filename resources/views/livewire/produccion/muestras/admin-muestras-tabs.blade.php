{{-- resources/views/livewire/admin/muestras/admin-muestras-tabs.blade.php --}}
<div x-data class="container mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Administración de Muestras</h2>

    {{-- Tabs --}}
    <div class="w-full overflow-x-auto">
        <div class="inline-flex space-x-2 bg-white rounded-lg p-1 shadow">
            @php
                $tabs = [
                    'PENDIENTE'     => 'bg-yellow-100 text-yellow-800',
                    'SOLICITADA'    => 'bg-blue-100 text-blue-800',
                    'MUESTRA LISTA' => 'bg-emerald-100 text-emerald-800',
                    'ENTREGADA'     => 'bg-green-100 text-green-800',
                    'CANCELADA'     => 'bg-gray-100 text-gray-800',
                ];
            @endphp

            @foreach($tabs as $name => $classes)
                <button
                    wire:click="setTab('{{ $name }}')"
                    class="px-3 py-2 rounded-md text-sm font-semibold transition
                           {{ $tab === $name ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    <span>{{ $name }}</span>
                    <span class="ml-2 inline-flex items-center justify-center text-xs font-bold rounded-full px-2 py-0.5 {{ $classes }}">
                        {{ $counts[$name] ?? 0 }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Contenido por pestaña: cada una es un componente hijo distinto --}}
    <div class="mt-4">
        @switch($tab)
            @case('PENDIENTE')
                @livewire('produccion.muestras.tab-pendiente', key('tab-pendiente'))
                @break

            @case('SOLICITADA')
                @livewire('produccion.muestras.tab-solicitada', key('tab-solicitada'))
                @break

            @case('MUESTRA LISTA')
                @livewire('produccion.muestras.tab-lista', key('tab-lista'))
                @break

            @case('ENTREGADA')
                @livewire('produccion.muestras.tab-entregada', key('tab-entregada'))
                @break

            @case('CANCELADA')
                @livewire('produccion.muestras.tab-cancelada', key('tab-cancelada'))
                @break
        @endswitch
    </div>

    {{-- Scripts encapsulados --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('livewire:navigated', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
    </script>
</div>
