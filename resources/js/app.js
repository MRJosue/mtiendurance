import './bootstrap';
import '../../vendor/rappasoft/laravel-livewire-tables/resources/imports/laravel-livewire-tables-all.js';


console.log('Hola soy app js');
// import Alpine from 'alpinejs';


// import Alpine from 'alpinejs';

// window.Alpine = Alpine;

// Alpine.start();

import Echo from "laravel-echo";
import Pusher from "pusher-js";
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: "pusher",
    key: "c2b1b3f693c74aa5f2ccfa3ed043b8a1",
    wsHost: window.location.hostname,
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
    cluster: "mt1",
});

window.Echo.channel("chat").listen("MessageSent", (e) => {
    console.log("Message received: ", e.message);
});