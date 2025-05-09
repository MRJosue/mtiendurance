<div x-data="{ open: true }" class="bg-white shadow rounded-lg border border-gray-200 mb-4">
    <div class="flex items-center justify-between px-4 py-2 border-b bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-700">{{ $titulo }}</h2>
        <button
            x-on:click="open = !open"
            class="text-sm text-blue-500 hover:underline focus:outline-none"
        >
            <span x-show="open">Minimizar</span>
            <span x-show="!open">Maximizar</span>
        </button>
    </div>

    <div x-show="open" x-transition class="p-4">
        {{ $slot }}
    </div>
</div>
