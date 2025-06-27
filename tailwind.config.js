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
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './app/Http/Livewire/**/*.php',
  ],
safelist: [
  'bg-blue-600',
  'hover:bg-blue-700',
  'bg-red-500',
  'hover:bg-red-600',
  'bg-gray-500',
  'hover:bg-gray-600',
  'bg-green-600',
  'hover:bg-green-700',
  'bg-yellow-500',
  'hover:bg-yellow-600',
  'bg-amber-500',
  'hover:bg-amber-600',
  'bg-sky-700',
  'hover:bg-sky-800',
  'bg-emerald-600',
  'hover:bg-emerald-700',
  'text-white',
  'text-black',
  'rounded',
  'px-4',
  'py-2',
  'px-3',
  'py-1.5',
  'w-full',
  'sm:w-auto'
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
