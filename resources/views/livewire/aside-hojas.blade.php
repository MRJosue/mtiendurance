<nav class="space-y-1">
    @foreach($hojas as $h)
        @php
            $isActive = request()->routeIs('produccion.hojas.show') && (request()->route('hoja')?->slug === $h->slug);
        @endphp
        <a href="{{ route('produccion.hojas.show', $h->slug) }}"
          class="block px-2 py-1 rounded hover:bg-gray-800">
            {{ $h->nombre }}
        </a>
    @endforeach
</nav>
