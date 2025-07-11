{{-- <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Demo Websockets</title>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
</head>
<body>
    <h1>Escuchando "canal-demo"...</h1>

<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
    const pusher = new Pusher('clave-demo-websockets', {
        cluster: 'mt1',
        wsHost: window.location.hostname,
        wsPort: 6001,
        forceTLS: false,
        encrypted: false,
        enabledTransports: ['ws'], // 🔥 IMPORTANTE: solo WS (no WSS)
        disableStats: true
    });

    const channel = pusher.subscribe('canal-demo');
    channel.bind('evento.demo', function (data) {
        console.log('📡 Evento recibido:', data.mensaje);
    });
</script>

</body>
</html> --}}

<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
  </head>
  <body>
    <div class="bg-red-500 md:bg-green-500 p-8">
      Resize el navegador: debería cambiar de rojo (mobile) a verde (desktop).
    </div>
  </body>
</html>