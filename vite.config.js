// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: [
//                 'resources/views/**/*.blade.php',
//                 'app/Http/Livewire/**',
//             ],
//         }),
//     ],
// });

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';



export default defineConfig({
  plugins: [
    laravel({
    //   input: ['resources/js/app.js'],
      input: ['resources/css/app.css','resources/js/app.js'],
      refresh: true,
    }),
  ],
})