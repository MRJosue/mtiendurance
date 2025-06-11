// ✅ CommonJS para que Tailwind lo cargue correctamente
const defaultTheme = require('tailwindcss/defaultTheme')
const forms       = require('@tailwindcss/forms')

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/rappasoft/laravel-livewire-tables/resources/views/**/*.blade.php',
    './vendor/wireui/wireui/resources/**/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './app/Livewire/**/*.php',
  ],
  safelist: [
    'bg-blue-600','hover:bg-blue-700',
    'bg-red-500','hover:bg-red-600',
    /* … todas tus clases safelist … */
    'w-full','sm:w-auto',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [ forms ],
}