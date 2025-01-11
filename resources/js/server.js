const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const { createClient } = require('redis');
const { createAdapter } = require('@socket.io/redis-adapter');

// Configuración de Express
const app = express();
const server = http.createServer(app);
const io = new Server(server);

// Configuración de Redis
const pubClient = createClient({ url: 'redis://localhost:6379' });
const subClient = pubClient.duplicate();

(async () => {
    await pubClient.connect();
    await subClient.connect();
    console.log('Conectado a Redis');
    io.adapter(createAdapter(pubClient, subClient));
})();

// Rutas básicas
app.get('/', (req, res) => {
    res.sendFile(__dirname + '/index.html');
});

// Manejo de conexiones Socket.IO
io.on('connection', (socket) => {
    console.log('Un usuario se ha conectado:', socket.id);

    // Recibe mensajes del cliente
    socket.on('mensaje', (msg) => {
        console.log('Mensaje recibido:', msg);
        io.emit('mensaje', msg); // Reenvía el mensaje a todos los clientes
    });

    socket.on('disconnect', () => {
        console.log('Un usuario se ha desconectado:', socket.id);
    });
});

// Iniciar el servidor
server.listen(3000, () => {
    console.log('Servidor corriendo en http://localhost:3000');
});