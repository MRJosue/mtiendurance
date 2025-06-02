const defaultTheme = require('tailwindcss/defaultTheme')
const forms = require('@tailwindcss/forms')

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/rappasoft/laravel-livewire-tables/resources/views/**/*.blade.php',
    './vendor/wireui/wireui/resources/**/*.blade.php', // 👈 Si usas WireUI
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './app/Livewire/**/*.php', // 👈 Para componentes Livewire
  ],
    safelist: [
    'bg-blue-600',
    'hover:bg-blue-700',
    'text-white',
    'rounded',
    'px-4',
    'py-2',
    'w-full'
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
