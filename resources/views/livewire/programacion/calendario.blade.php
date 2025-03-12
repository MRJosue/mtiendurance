<div class="bg-white p-4 shadow-lg rounded-lg">
    <h2 class="text-lg font-semibold mb-4">Calendario de Producción</h2>
    <div id='calendar'></div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Livewire.on('pedidoActualizado', () => {
            calendar.refetchEvents();
        });

        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            events: @json($eventos),
        });
        calendar.render();
    });
</script>