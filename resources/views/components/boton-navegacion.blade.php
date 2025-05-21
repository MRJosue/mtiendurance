<div
    x-data
    class="flex flex-wrap gap-2 justify-end"
>
    <!-- Botón Regresar -->
    <button
        id="btn-atras"
        @click="window.history.back()"
        class="flex items-center gap-2 px-4 py-2 bg-stone-100 text-stone-800 border border-stone-300 rounded-lg hover:bg-stone-200 disabled:opacity-50 disabled:cursor-not-allowed"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-stone-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Regresar
    </button>

    <!-- Botón Avanzar -->
    <button
        id="btn-adelante"
        @click="window.history.forward()"
        class="flex items-center gap-2 px-4 py-2 bg-stone-100 text-stone-800 border border-stone-300 rounded-lg hover:bg-stone-200 disabled:opacity-50 disabled:cursor-not-allowed"
    >
        Avanzar
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-stone-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const backBtn = document.getElementById('btn-atras');
        const forwardBtn = document.getElementById('btn-adelante');

        backBtn.disabled = window.history.length <= 1;
    });
</script>
