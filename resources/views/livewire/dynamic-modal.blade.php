<div x-data="{ open: false }" x-init="$watch('componentName', value => open = !!value)">
    <div x-show="open" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
        <div class="bg-white p-6 rounded shadow-lg">
            <button @click="open = false; $wire.componentName = null;" class="absolute top-2 right-2">✖</button>
            @if ($componentName)
                @livewire($componentName, ['id' => $componentId])
            @endif
        </div>
    </div>
</div>
