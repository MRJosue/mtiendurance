import './bootstrap';
import '../../vendor/rappasoft/laravel-livewire-tables/resources/imports/laravel-livewire-tables-all.js';

// import Alpine from 'alpinejs';

// window.Alpine = Alpine;

// Alpine.start();
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
    wsHost: window.location.hostname,
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
});


import { io } from "socket.io-client";

const socket = io('http://localhost:3000'); // Cambia el host si tu servidor está en otro dominio

socket.on('chat:message', (data) => {
    Livewire.emit('receiveMessage', data); // Emitir evento a Livewire
});

window.socket = socket; // Hacer accesible el cliente en todo el proyecto


