<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script>
        (() => {
            const theme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const shouldUseDark = theme ? theme === 'dark' : prefersDark;

            document.documentElement.classList.toggle('dark', shouldUseDark);
            document.documentElement.setAttribute('data-theme', shouldUseDark ? 'dark' : 'light');
        })();
    </script>
    @vite(['resources/css/app.css','resources/js/app.js'])

    @livewireStyles
    @wireUiScripts

</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 overflow-x-hidden">
    {{-- ASIDE (incluye su propia lógica Alpine) --}}
    @include('layouts.aside')

    {{-- CONTENIDO PRINCIPAL: padding dinámico segun estado del aside --}}
    <div
        x-data="{ asideOpen: true }"
        @aside:state.window="asideOpen = $event.detail.open"
        :class="asideOpen ? 'lg:pl-64' : 'lg:pl-0'"
        class="min-h-screen flex flex-col relative transition-[padding] duration-300 ease-in-out"
    >

            <div class="absolute top-4 right-4 z-40">
                <div class="flex items-center gap-2 sm:gap-3">
                    <button
                        type="button"
                        x-data
                        @click="$store.theme.toggle()"
                        class="inline-flex items-center gap-2 rounded-md bg-white/90 px-3 py-2 text-sm font-medium text-gray-700 shadow transition hover:bg-white dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        aria-label="Cambiar tema"
                    >
                        <svg x-show="$store.theme.current === 'light'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.114 6.364-1.591-1.591M7.477 7.477 5.886 5.886m12.228 0-1.591 1.591M7.477 16.523l-1.591 1.591M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="$store.theme.current === 'dark'" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3c-.008.13-.01.26-.01.39A7.5 7.5 0 0018.61 10.8c.13 0 .26-.002.39-.01z" />
                        </svg>
                        <span x-text="$store.theme.current === 'dark' ? 'Oscuro' : 'Claro'"></span>
                    </button>

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

                        {{-- Menú de idioma --}}
                        <div
                            x-cloak
                            x-show="openLang"
                            @click.outside="openLang = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
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


        @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="flex-1">
            {{ $slot }}
        </main>

        <x-boton-navegacion />
        @livewire('cambiar-rol-actual')
    </div>

    <div
        id="session-warning-modal"
        class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/60 px-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="session-warning-title"
    >
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
            <div class="space-y-3">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-600 dark:text-amber-400">
                    Sesion por expirar
                </p>
                <h2 id="session-warning-title" class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                    Tu sesion esta a punto de vencer
                </h2>
                <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Para evitar perder el contexto, confirma si deseas seguir trabajando. Si no respondes, te enviaremos a iniciar sesion.
                </p>
                <p class="rounded-xl bg-slate-100 px-4 py-3 text-sm font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    Tiempo restante estimado:
                    <span id="session-warning-countdown" class="font-semibold text-amber-600 dark:text-amber-300">--:--</span>
                </p>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <button
                    id="session-keepalive-button"
                    type="button"
                    class="inline-flex flex-1 items-center justify-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                >
                    Continuar sesion
                </button>
                <button
                    id="session-login-button"
                    type="button"
                    class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus:ring-offset-slate-900"
                >
                    Ir a login
                </button>
            </div>

            <p id="session-warning-feedback" class="mt-3 hidden text-sm text-slate-500 dark:text-slate-400"></p>
        </div>
    </div>

    @livewireScripts

    @stack('scripts')
    <x-notify::notify />
    @notifyJs

    <script>
    document.addEventListener('DOMContentLoaded', () => {
    const loginUrl = "{{ route('login') }}";
    const heartbeatUrl = "{{ route('session.heartbeat') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const sessionLifetimeMs = {{ (int) config('session.lifetime') * 60 * 1000 }};
    const warningLeadMs = Math.min(5 * 60 * 1000, Math.max(60 * 1000, Math.floor(sessionLifetimeMs / 6)));
    const heartbeatIntervalMs = Math.min(5 * 60 * 1000, Math.max(2 * 60 * 1000, Math.floor(sessionLifetimeMs / 4)));
    const storageKey = 'mti:last-session-confirmed-at';
    const modal = document.getElementById('session-warning-modal');
    const countdown = document.getElementById('session-warning-countdown');
    const keepaliveButton = document.getElementById('session-keepalive-button');
    const loginButton = document.getElementById('session-login-button');
    const feedback = document.getElementById('session-warning-feedback');

    let heartbeatPromise = null;
    let redirecting = false;
    let lastConfirmedAt = Date.now();

    const isSameOrigin = (resource) => {
        try {
            const url = typeof resource === 'string'
                ? new URL(resource, window.location.origin)
                : new URL(resource?.url ?? '', window.location.origin);

            return url.origin === window.location.origin;
        } catch (error) {
            return true;
        }
    };

    const setFeedback = (message = '', tone = 'neutral') => {
        if (!feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.classList.toggle('hidden', !message);
        feedback.classList.toggle('text-rose-500', tone === 'error');
        feedback.classList.toggle('text-emerald-600', tone === 'success');
        feedback.classList.toggle('text-slate-500', tone === 'neutral');
        feedback.classList.toggle('dark:text-rose-300', tone === 'error');
        feedback.classList.toggle('dark:text-emerald-300', tone === 'success');
        feedback.classList.toggle('dark:text-slate-400', tone === 'neutral');
    };

    const formatRemaining = (ms) => {
        const totalSeconds = Math.max(0, Math.ceil(ms / 1000));
        const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
        const seconds = String(totalSeconds % 60).padStart(2, '0');

        return `${minutes}:${seconds}`;
    };

    const showModal = () => {
        if (!modal || !modal.classList.contains('hidden')) {
            return;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const hideModal = () => {
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        setFeedback();
    };

    const syncConfirmedAt = (timestamp = Date.now()) => {
        lastConfirmedAt = timestamp;
        window.localStorage.setItem(storageKey, String(timestamp));
        hideModal();
    };

    const goLogin = (reason = 'session-expired') => {
        if (redirecting) {
            return;
        }

        redirecting = true;
        window.location.href = `${loginUrl}?reason=${encodeURIComponent(reason)}`;
    };

    const markResponseAsHealthy = (resource, status) => {
        if (!isSameOrigin(resource)) {
            return;
        }

        if (status >= 200 && status < 400) {
            syncConfirmedAt(Date.now());
        }
    };

    const heartbeat = async ({ force = false } = {}) => {
        if (document.hidden && !force) {
            return false;
        }

        if (heartbeatPromise) {
            return heartbeatPromise;
        }

        keepaliveButton?.setAttribute('disabled', 'disabled');
        setFeedback('Verificando tu sesion...', 'neutral');

        heartbeatPromise = window.fetch(heartbeatUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                source: force ? 'manual' : 'automatic',
            }),
        }).then(async (response) => {
            if (response.status === 401 || response.status === 419) {
                goLogin();
                return false;
            }

            if (!response.ok) {
                throw new Error(`Heartbeat failed with status ${response.status}`);
            }

            const payload = await response.json().catch(() => ({}));
            const serverTime = payload.server_time ? Date.parse(payload.server_time) : Date.now();

            syncConfirmedAt(Number.isNaN(serverTime) ? Date.now() : serverTime);
            setFeedback('Sesion extendida correctamente.', 'success');

            return true;
        }).catch(() => {
            setFeedback('No pudimos renovar la sesion. Revisa tu conexion o inicia sesion de nuevo si el problema continua.', 'error');
            return false;
        }).finally(() => {
            heartbeatPromise = null;
            keepaliveButton?.removeAttribute('disabled');
        });

        return heartbeatPromise;
    };

    const updateWarningState = () => {
        const remainingMs = (lastConfirmedAt + sessionLifetimeMs) - Date.now();

        if (countdown) {
            countdown.textContent = formatRemaining(remainingMs);
        }

        if (remainingMs <= 0) {
            if (heartbeatPromise) {
                return;
            }

            if (document.visibilityState === 'visible') {
                heartbeat({ force: true }).then((ok) => {
                    if (!ok) {
                        goLogin();
                    }
                });

                return;
            }

            goLogin();
            return;
        }

        if (remainingMs <= warningLeadMs) {
            showModal();
            return;
        }

        hideModal();
    };

    const origFetch = window.fetch;
    window.fetch = async (...args) => {
        const res = await origFetch(...args);
        if (res.status === 401 || res.status === 419) {
            goLogin();
        } else {
            markResponseAsHealthy(args[0], res.status);
        }

        return res;
    };

    const origOpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function(...args) {
        this.__mtiRequestUrl = args[1];
        this.addEventListener('load', function() {
        if (this.status === 401 || this.status === 419) {
            goLogin();
        } else {
            markResponseAsHealthy(this.__mtiRequestUrl, this.status);
        }
        });
        return origOpen.apply(this, args);
    };

    keepaliveButton?.addEventListener('click', () => {
        heartbeat({ force: true });
    });

    loginButton?.addEventListener('click', () => {
        goLogin();
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            heartbeat({ force: true });
        }
    });

    window.addEventListener('focus', () => {
        heartbeat({ force: true });
    });

    window.addEventListener('storage', (event) => {
        if (event.key !== storageKey || !event.newValue) {
            return;
        }

        const timestamp = Number(event.newValue);

        if (!Number.isNaN(timestamp)) {
            lastConfirmedAt = timestamp;
            hideModal();
        }
    });

    window.setInterval(updateWarningState, 1000);
    window.setInterval(() => {
        heartbeat();
    }, heartbeatIntervalMs);

    syncConfirmedAt(Date.now());
    updateWarningState();
    });
    </script>

    <style>[x-cloak]{ display:none !important; }</style>
</body>

</html>
