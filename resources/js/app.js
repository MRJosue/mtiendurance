import './bootstrap';
import '../css/app.css';

import Echo from "laravel-echo";
import Pusher from "pusher-js";

import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';




console.log('Hola soy app js');


window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: "pusher",
    key: "c2b1b3f693c74aa5f2ccfa3ed043b8a1",
    wsHost: window.location.hostname,
    wsPort: 6001,
    encrypted:false,
    disableStats: true,
    cluster: "mt1",
});



// quitamos forceTLS: false,
// y usamos encrypted:false

window.Echo.channel("chat").listen("MessageSent", (e) => {
    console.log("Message received: ", e.message);
});


