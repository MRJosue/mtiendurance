<!DOCTYPE html>
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
            wssPort: 6001,
            forceTLS: true,
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
        });
    
        const channel = pusher.subscribe('canal-demo');
        channel.bind('evento.demo', function (data) {
            console.log('📡 Evento recibido:', data.mensaje);
        });
    </script>
    
</body>
</html>