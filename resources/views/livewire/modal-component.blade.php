<div wire:key="modal-{{ $id }}">
    <!-- Botón para abrir el modal -->
    <button wire:click="show" class="btn btn-primary">{{$titulo}}</button>

    <!-- Modal -->
    @if ($showModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white p-8 rounded-lg shadow-lg w-96">


                @if ($component)
                @livewire($component,  [$methodname => $id])
                @endif
                <button wire:click="close" class="btn btn-danger mt-4">Cerrar</button>
            </div>
        </div>
    @endif
</div>
