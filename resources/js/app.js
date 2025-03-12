import './bootstrap';
import Echo from "laravel-echo";
import Pusher from "pusher-js";
//import '../../vendor/rappasoft/laravel-livewire-tables/resources/imports/laravel-livewire-tables-all.js';


console.log('Hola soy app js');






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



function configurarFechasPermitidas(input) {
    const hoy = new Date();
    let fechaMinima = hoy.toISOString().split('T')[0]; // Fecha mínima es hoy
    input.min = fechaMinima; 

    input.addEventListener('input', function () {
        let fechaSeleccionada = new Date(this.value);
        let diaSemana = fechaSeleccionada.getDay(); // 0 = Domingo, 6 = Sábado

        if (diaSemana === 0 || diaSemana === 6) {
            let nuevaFecha = fechaSeleccionada;
            
            // Si es sábado, mueve la fecha al próximo lunes
            if (diaSemana === 6) {
                nuevaFecha.setDate(nuevaFecha.getDate() + 2);
            } 
            // Si es domingo, mueve la fecha al próximo lunes
            else if (diaSemana === 0) {
                nuevaFecha.setDate(nuevaFecha.getDate() + 1);
            }

            // Establece la nueva fecha en el input
            this.value = nuevaFecha.toISOString().split('T')[0];
        }
    });
}

document.addEventListener('livewire:navigated', () => {
    let inputFecha = document.querySelector('input[type="date"][wire\\:model="fecha_entrega"]');
    if (inputFecha) {
        configurarFechasPermitidas(inputFecha);
    }
});
