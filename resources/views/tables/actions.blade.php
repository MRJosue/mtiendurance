<div>

@livewire('modal-component', ['id' => $row->id, 'component' => $component,'titulo' => $titulo], key('modal-'.$row->id))

</div>

@push('scripts')

@endpush
