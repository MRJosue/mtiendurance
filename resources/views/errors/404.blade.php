@php
    $title = 'Pagina no disponible';
    $message = $errorMessage ?? 'No encontramos la pagina solicitada.';
@endphp

@auth
<x-app-layout>
    <div class="mx-auto flex min-h-[70vh] max-w-4xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="w-full overflow-hidden rounded-3xl border border-amber-200 bg-white shadow-xl dark:border-amber-900/60 dark:bg-gray-800">
            <div class="border-b border-amber-100 bg-gradient-to-r from-amber-50 via-white to-orange-50 px-6 py-6 dark:border-amber-900/50 dark:from-amber-950/50 dark:via-gray-800 dark:to-orange-950/40">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600 dark:text-amber-400">404</p>
                <h1 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
                <p class="mt-3 max-w-2xl text-sm text-gray-600 dark:text-gray-300">{{ $message }}</p>
            </div>

            <div class="space-y-4 px-6 py-6 text-sm text-gray-600 dark:text-gray-300">
                <p>La sesion sigue activa y la barra de navegacion permanece disponible para que regreses a otra seccion.</p>

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ url()->previous() }}"
                        class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white transition hover:bg-amber-600"
                    >
                        Regresar
                    </a>

                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        Ir al dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
@else
<x-guest-layout>
    <div class="mx-auto flex min-h-[70vh] max-w-3xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="w-full rounded-3xl border border-amber-200 bg-white p-8 shadow-xl dark:border-amber-900/60 dark:bg-gray-800">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600 dark:text-amber-400">404</p>
            <h1 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ $message }}</p>
        </div>
    </div>
</x-guest-layout>
@endauth
