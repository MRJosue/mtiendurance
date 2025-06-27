const defaultTheme = require('tailwindcss/defaultTheme')
const forms = require('@tailwindcss/forms')
module.exports = {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/rappasoft/laravel-livewire-tables/resources/views/**/*.blade.php',
    './vendor/wireui/wireui/resources/**/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',       // ← sin duplicados
    './resources/js/**/*.js',                // tus scripts
    './resources/**/*.vue',                  // si usas Vue
    './app/Http/Livewire/**/*.php',          // tus componentes Livewire
  ],
  safelist: [
    // tus colores/carcasas dinámicas
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [forms],
}