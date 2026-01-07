<div class="absolute top-4 right-4 z-40">
    <div class="flex items-center gap-2 sm:gap-3">

        {{-- Campana de notificaciones --}}
        <div class="relative">
            @livewire('notificaciones.notificaciones')
        </div>

        {{-- Dropdown de idioma --}}
        <div x-data="{openLang:false}" class="relative">
            <button
                @click="openLang = !openLang"
                class="inline-flex items-center gap-2 rounded-md bg-white/90 px-3 py-2 text-sm font-medium text-gray-700 shadow hover:bg-white
                    dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                aria-label="{{ __('menu.open_menu') }}"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 3a9 9 0 100 18 9 9 0 000-18zm0 0c2.5 2.5 2.5 15.5 0 18m0-18C9.5 5.5 9.5 18.5 12 21m-9-9h18" />
                </svg>
                <span class="uppercase leading-none">{{ app()->getLocale() }}</span>
                <svg class="h-4 w-4 opacity-70" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                </svg>
            </button>

            <div
                x-cloak
                x-show="openLang"
                @click.outside="openLang = false"
                x-transition
                class="absolute right-0 mt-2 w-56 rounded-md bg-white shadow-lg ring-1 ring-black/5 dark:bg-gray-800"
            >
                <div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-300">{{ __('Idioma') }}</div>
                <a href="{{ route('lang.switch','es') }}" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                    Español (ES)
                </a>
                <a href="{{ route('lang.switch','en') }}" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                    English (EN)
                </a>
            </div>
        </div>
    </div>
</div>
