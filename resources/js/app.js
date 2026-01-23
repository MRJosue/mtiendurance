import '../css/app.css';

import './bootstrap';


import Echo from "laravel-echo";
import Pusher from "pusher-js";

import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';

 
import imageZoom from './components/image-zoom';

window.imageZoom = imageZoom;
console.log('Hola soy app js');

// resources/js/app.js
import '../css/app.css'

// Si ya tienes bootstrap.js u otras importaciones, déjalas:
import './bootstrap'  // si existe

// import Alpine from 'alpinejs'
// window.Alpine = Alpine

// Crea el store UI cuando Alpine se inicialice
document.addEventListener('alpine:init', () => {
  // Evita recrear el store si haces HMR
  if (!Alpine.store('ui')) {
    Alpine.store('ui', {
      sidebarOpen: window.innerWidth >= 1024,   // abierto en desktop
      sidebarForced: false,                     // si el usuario lo cerró manualmente en móvil
      openSections: JSON.parse(localStorage.getItem('openSections') || '{}'),
      // Si quieres tener la ruta actual disponible en JS puro:
      selectedRoute: document.body.dataset.routeName || '',

      toggleSection(name) {
        this.openSections[name] = !this.openSections[name];
        localStorage.setItem('openSections', JSON.stringify(this.openSections));
      },
      isActive(route) {
        return this.selectedRoute === route;
      }
    });

    // ÚNICO listener de resize
    window.addEventListener('resize', () => {
      if (window.innerWidth >= 1024) {
        Alpine.store('ui').sidebarOpen = true;
      } else if (Alpine.store('ui').sidebarForced) {
        Alpine.store('ui').sidebarOpen = false;
      }
    });
  }
});

// ¡Arranca Alpine!
// Alpine.start()

// window.Pusher = Pusher;
// window.Echo = new Echo({
//     broadcaster: "pusher",
//     key: "c2b1b3f693c74aa5f2ccfa3ed043b8a1",
//     wsHost: window.location.hostname,
//     wsPort: 6001,
//     encrypted:false,
//     disableStats: true,
//     cluster: "mt1",
// });



// // quitamos forceTLS: false,
// // y usamos encrypted:false

// window.Echo.channel("chat").listen("MessageSent", (e) => {
//     console.log("Message received: ", e.message);
// });


