{{-- resources/views/livewire/admin/muestras/admin-muestras-tabs.blade.php --}}

<div x-data class="container mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Administración de Muestras</h2>

    @php
        // Mapear pestañas a clases y permisos
        $tabs = [
            'PENDIENTE'     => ['classes' => 'bg-yellow-100 text-yellow-800', 'perm' => 'asideAdministraciónMuestrasTabPendiente'],
            'SOLICITADA'    => ['classes' => 'bg-blue-100 text-blue-800',     'perm' => 'asideAdministraciónMuestrasTabSoliocitada'],
            'MUESTRA LISTA' => ['classes' => 'bg-emerald-100 text-emerald-800','perm' => 'asideAdministraciónMuestrasTabMuestraLista'],
            'ENTREGADA'     => ['classes' => 'bg-green-100 text-green-800',   'perm' => 'asideAdministraciónMuestrasTabEntregada'],
            'CANCELADA'     => ['classes' => 'bg-gray-100 text-gray-800',     'perm' => 'asideAdministraciónMuestrasTabCancelada'],
        ];

        // ¿El usuario tiene permiso para la pestaña actual?
        $currentPerm = $tabs[$tab]['perm'] ?? null;
        $hasCurrentPerm = $currentPerm ? auth()->user()?->can($currentPerm) : false;
    @endphp

    {{-- Tabs --}}
    <div class="w-full overflow-x-auto">
        <div class="inline-flex space-x-2 bg-white rounded-lg p-1 shadow">
            @foreach($tabs as $name => $cfg)
                @can($cfg['perm'])
                    <button
                        wire:click="setTab('{{ $name }}')"
                        class="px-3 py-2 rounded-md text-sm font-semibold transition
                               {{ $tab === $name ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        <span>{{ $name }}</span>
                        <span class="ml-2 inline-flex items-center justify-center text-xs font-bold rounded-full px-2 py-0.5 {{ $cfg['classes'] }}">
                            {{ $counts[$name] ?? 0 }}
                        </span>
                    </button>
                @endcan
            @endforeach
        </div>
    </div>

    {{-- Contenido por pestaña (con verificación de permisos) --}}
    <div class="mt-4">
        @if($hasCurrentPerm)
            @switch($tab)
                @case('PENDIENTE')
                    @can('asideAdministraciónMuestrasTabPendiente')
                        @livewire('produccion.muestras.tab-pendiente', key('tab-pendiente'))
                    @endcan
                    @break

                @case('SOLICITADA')
                    @can('asideAdministraciónMuestrasTabSoliocitada')
                        @livewire('produccion.muestras.tab-solicitada', key('tab-solicitada'))
                    @endcan
                    @break

                @case('MUESTRA LISTA')
                    @can('asideAdministraciónMuestrasTabMuestraLista')
                        @livewire('produccion.muestras.tab-lista', key('tab-lista'))
                    @endcan
                    @break

                @case('ENTREGADA')
                    @can('asideAdministraciónMuestrasTabEntregada')
                        @livewire('produccion.muestras.tab-entregada', key('tab-entregada'))
                    @endcan
                    @break

                @case('CANCELADA')
                    @can('asideAdministraciónMuestrasTabCancelada')
                        @livewire('produccion.muestras.tab-cancelada', key('tab-cancelada'))
                    @endcan
                    @break
            @endswitch
        @else
            {{-- <div class="p-4 bg-yellow-50 border border-yellow-200 rounded text-yellow-800">
                No tienes permiso para ver esta pestaña.
            </div> --}}
        @endif
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
