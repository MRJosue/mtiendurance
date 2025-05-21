<div
    x-data
    class="flex flex-wrap gap-2 justify-end"
>
    <button
        id="btn-atras"
        @click="window.history.back()"
        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
    >
        ⬅ Regresar
    </button>

    <button
        id="btn-adelante"
        @click="window.history.forward()"
        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
    >
        Avanzar ➡
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const backBtn = document.getElementById('btn-atras');
        const forwardBtn = document.getElementById('btn-adelante');

        // Opcional: desactivar si no hay historial
        backBtn.disabled = window.history.length <= 1;
        // No hay forma fiable de saber si hay adelante, así que lo dejamos activo
    });
</script>
