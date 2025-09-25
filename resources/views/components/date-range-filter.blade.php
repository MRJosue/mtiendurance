@props([
    'label' => 'Rango de fechas',
    // Campo base: p.ej. "fecha_produccion" → usa filters.fecha_produccion_from / _to
    'field' => '',
    // Si quieres override explícito de los modelos Livewire:
    'from' => null,           // ej: 'filters.mi_from'
    'to'   => null,           // ej: 'filters.mi_to'
    // Extra classes para contenedor
    'class' => '',
    // Tamaño: sm | md
    'size' => 'sm',
])

@php
    $fromModel = $from ?: "filters.{$field}_from";
    $toModel   = $to   ?: "filters.{$field}_to";

    $inputClasses = $size === 'sm'
        ? 'w-40 rounded-lg border-gray-300 focus:ring-blue-500 text-sm'
        : 'w-48 rounded-lg border-gray-300 focus:ring-blue-500';

    $labelClasses = 'text-xs text-gray-600';
@endphp

<div class="flex flex-col gap-2 p-2 {{ $class }}">
    @if($label)
        <label class="{{ $labelClasses }}">{{ $label }}</label>
    @endif

    <div class="flex flex-col space-y-1">
        <input
            type="date"
            class="{{ $inputClasses }}"
            wire:model.live.debounce.400ms="{{ $fromModel }}"
            placeholder="Desde"
        >
        <input
            type="date"
            class="{{ $inputClasses }}"
            wire:model.live.debounce.400ms="{{ $toModel }}"
            placeholder="Hasta"
        >
    </div>

    <div class="pt-1">
        <button
            type="button"
            class="px-2 py-1 text-xs rounded-lg border bg-white hover:bg-gray-50"
            {{-- Limpia ambos campos del rango --}}
            wire:click="$set('{{ $fromModel }}', null); $set('{{ $toModel }}', null)"
        >
            Limpiar
        </button>
    </div>
</div>
