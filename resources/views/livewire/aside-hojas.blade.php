<nav class="space-y-1">
    @foreach($hojas as $h)
        @php
            $isActive = request()->routeIs('produccion.hojas.show')
                && (request()->route('hoja')?->slug === $h->slug);

            $menu = $h->menu_config ?? [];
                // Si etiqueta está vacía (null, '', espacios), usa el nombre
            $etq   = data_get($menu, 'etiqueta');
            $label = (is_string($etq) && trim($etq) !== '') ? $etq : $h->nombre;

            
            $icon  = $menu['icono']   ?? null;
        @endphp

        <a href="{{ route('produccion.hojas.show', $h->slug) }}"
           class="block px-2 py-1 rounded hover:bg-gray-800
                  {{ $isActive ? 'bg-gray-100 dark:bg-gray-800 font-semibold' : '' }}">
            @if($icon)
                {{-- Render simple del icono (ajusta a tu librería real si usas heroicons/lucide) --}}
                <span class="inline-block text-sm opacity-70 group-hover:opacity-100">
                    {{ $icon }}
                </span>
            @endif
            <span class="truncate">{{ $label }}</span>
        </a>
    @endforeach

    @if($hojas->isEmpty())
        <div class="text-sm text-gray-500 px-2 py-1">Sin hojas para esta ubicación.</div>
    @endif
</nav>
{{-- <nav class="space-y-1">
    @foreach($hojas as $h)
        @php
            $isActive = request()->routeIs('produccion.hojas.show') && (request()->route('hoja')?->slug === $h->slug);
        @endphp
        <a href="{{ route('produccion.hojas.show', $h->slug) }}"
          class="block px-2 py-1 rounded hover:bg-gray-800">
            {{ $h->nombre }}
        </a>
    @endforeach
</nav> --}}
