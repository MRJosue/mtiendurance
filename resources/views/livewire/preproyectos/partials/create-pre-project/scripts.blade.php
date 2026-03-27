@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    Livewire.on('usuario-cambiado', ({ id }) => {
        // Hook disponible para side-effects locales.
    });

    Livewire.on('setReadOnlyMode', () => {
        setTimeout(function () {
            $("input, textarea").attr("readonly", "readonly");
            $("select, button").attr("disabled", "disabled");
        }, 100);
    });

    Livewire.on('redirect', (url) => { window.location.href = url; });
});
</script>
@endpush
