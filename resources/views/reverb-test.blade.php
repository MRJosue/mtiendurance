<!doctype html>

<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Reverb Test</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100">
    <div class="max-w-3xl mx-auto p-4 sm:p-6">
        <div class="bg-white rounded-xl shadow p-4 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Prueba Laravel Reverb</h1>
                <span id="status"
                      class="text-xs sm:text-sm px-3 py-1 rounded-full bg-gray-200 text-gray-700 w-fit">
                    Conectando...
                </span>
            </div>

            <p class="text-sm text-gray-600">
                Abre esta misma URL en otro navegador (o incógnito). Envía un mensaje aquí y deberías verlo aparecer en ambos.
            </p>

            <div class="flex flex-col sm:flex-row gap-2">
                <input
                    id="message"
                    type="text"
                    maxlength="500"
                    placeholder="Escribe un mensaje..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-blue-200"
                />
                <button
                    id="sendBtn"
                    class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Enviar
                </button>
            </div>

            <div class="border-t pt-4">
                <h2 class="text-sm font-semibold text-gray-700 mb-2">Mensajes recibidos:</h2>
                <div id="log" class="space-y-2 max-h-[50vh] overflow-y-auto pr-1">
                    <div class="text-sm text-gray-500">Aún no hay mensajes...</div>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusEl = document.getElementById('status');
    const logEl = document.getElementById('log');
    const inputEl = document.getElementById('message');
    const btnEl = document.getElementById('sendBtn');

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const setStatus = (text, ok) => {
        statusEl.textContent = text;
        statusEl.className = ok
            ? 'text-xs sm:text-sm px-3 py-1 rounded-full bg-green-100 text-green-800 w-fit'
            : 'text-xs sm:text-sm px-3 py-1 rounded-full bg-red-100 text-red-800 w-fit';
    };

    const addLog = (payload) => {
        if (logEl.firstElementChild && logEl.firstElementChild.classList.contains('text-gray-500')) {
            logEl.innerHTML = '';
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'p-3 rounded-lg border bg-gray-50';

        wrapper.innerHTML = `
            <div class="text-sm text-gray-800 break-words">${escapeHtml(payload.message)}</div>
            <div class="mt-1 text-xs text-gray-500">${escapeHtml(payload.sentAt ?? '')}</div>
        `;

        logEl.prepend(wrapper);
    };

    const escapeHtml = (str) => {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    };

    // ✅ Estado de conexión (Pusher)
    if (!window.Echo?.connector?.pusher) {
        statusEl.textContent = 'Echo no está cargado';
        return;
    }

    const conn = window.Echo.connector.pusher.connection;

    // Estado inicial + debug visual
    setStatus(conn.state === 'connected' ? 'Conectado' : `Conectando... (${conn.state})`, conn.state === 'connected');

    conn.bind('connected', () => setStatus('Conectado', true));
    conn.bind('disconnected', () => setStatus('Desconectado', false));
    conn.bind('error', () => setStatus('Error de conexión', false));
    conn.bind('state_change', (states) => {
        // states.current: connecting/connected/unavailable/disconnected...
        setStatus(states.current === 'connected' ? 'Conectado' : `Conectando... (${states.current})`, states.current === 'connected');
    });

    // ✅ Escuchar canal público
    window.Echo.channel('reverb-test')
        .listen('.message.sent', (e) => addLog(e));

    // ✅ Enviar mensaje
    const send = async () => {
        const message = inputEl.value.trim();
        if (!message) return;

        btnEl.disabled = true;

        try {
            const res = await fetch("{{ route('reverb.test.send') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message })
            });

            if (!res.ok) {
                const err = await res.text();
                addLog({ message: 'Error enviando: ' + err, sentAt: '' });
            } else {
                inputEl.value = '';
                inputEl.focus();
            }
        } catch (err) {
            addLog({ message: 'Error enviando: ' + err.message, sentAt: '' });
        } finally {
            btnEl.disabled = false;
        }
    };

    btnEl.addEventListener('click', send);
    inputEl.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') send();
    });
});
</script>

</body>
</html>
