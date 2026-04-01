@php
    $noLeidas = $notificaciones->whereNull('read_at')->count();
@endphp

<div wire:poll.15s="cargarNotificaciones" class="relative" x-data="{ open: false }">
    <button
        @click="open = !open"
        type="button"
        class="relative inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-blue-500/40 dark:hover:bg-gray-700 dark:hover:text-blue-400 dark:focus:ring-offset-gray-900"
        aria-label="Abrir notificaciones"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17H9.143m5.714 0H18l-1.286-1.286A2 2 0 0 1 16.143 14.3V11a4.143 4.143 0 1 0-8.286 0v3.3a2 2 0 0 1-.571 1.414L6 17h3.143m5.714 0a2.857 2.857 0 1 1-5.714 0" />
        </svg>

        @if($noLeidas > 0)
            <span class="absolute -right-1 -top-1 inline-flex min-h-[1.35rem] min-w-[1.35rem] items-center justify-center rounded-full bg-rose-600 px-1.5 text-[10px] font-semibold text-white shadow-sm ring-2 ring-white dark:ring-gray-800">
                {{ $noLeidas > 99 ? '99+' : $noLeidas }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        @click.away="open = false"
        x-cloak
        class="absolute right-0 mt-3 w-[22rem] overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl shadow-gray-200/70 z-50 dark:border-gray-700 dark:bg-gray-900 dark:shadow-black/30"
    >
        <div class="border-b border-gray-100 bg-gradient-to-r from-blue-50 via-white to-sky-50 px-5 py-4 dark:border-gray-800 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Notificaciones</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $noLeidas > 0 ? $noLeidas . ' pendientes por revisar' : 'Todo al corriente' }}
                    </p>
                </div>

                @if($noLeidas > 0)
                    <button
                        wire:click="marcarTodasComoLeidas"
                        type="button"
                        class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-1 text-[11px] font-medium text-gray-600 transition hover:border-blue-200 hover:text-blue-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-blue-500/40 dark:hover:text-blue-400"
                    >
                        Marcar todo
                    </button>
                @endif
            </div>
        </div>

        @if($notificaciones->isEmpty())
            <div class="px-5 py-10 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17H9.143m5.714 0H18l-1.286-1.286A2 2 0 0 1 16.143 14.3V11a4.143 4.143 0 1 0-8.286 0v3.3a2 2 0 0 1-.571 1.414L6 17h3.143m5.714 0a2.857 2.857 0 1 1-5.714 0" />
                    </svg>
                </div>
                <p class="mt-4 text-sm font-medium text-gray-700 dark:text-gray-200">No tienes notificaciones</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cuando llegue algo nuevo aparecerá aquí.</p>
            </div>
        @else
            <div class="max-h-[26rem] overflow-y-auto px-3 py-3">
                <ul class="space-y-2">
                    @foreach($notificaciones as $notificacion)
                        @php
                            $contenido = is_array($notificacion->data) ? $notificacion->data : (json_decode($notificacion->data, true) ?? []);
                            $mensaje = $contenido['mensaje'] ?? 'Notificación sin contenido';
                            $liga = $contenido['liga'] ?? null;
                            $leida = filled($notificacion->read_at);
                        @endphp

                        <li
                            class="rounded-2xl border p-3 transition {{ $leida
                                ? 'border-gray-200 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/60'
                                : 'border-blue-100 bg-blue-50/80 shadow-sm dark:border-blue-500/20 dark:bg-blue-500/10' }}"
                        >
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl {{ $leida
                                    ? 'bg-white text-gray-400 dark:bg-gray-700 dark:text-gray-300'
                                    : 'bg-blue-600 text-white dark:bg-blue-500' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17H9.143m5.714 0H18l-1.286-1.286A2 2 0 0 1 16.143 14.3V11a4.143 4.143 0 1 0-8.286 0v3.3a2 2 0 0 1-.571 1.414L6 17h3.143m5.714 0a2.857 2.857 0 1 1-5.714 0" />
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ class_basename($notificacion->type) }}
                                            </p>
                                            @unless($leida)
                                                <span class="rounded-full bg-blue-600/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">
                                                    Nueva
                                                </span>
                                            @endunless
                                        </div>

                                        <span class="shrink-0 text-[11px] text-gray-500 dark:text-gray-400">
                                            {{ $notificacion->created_at?->diffForHumans() }}
                                        </span>
                                    </div>

                                    <p class="mt-1 text-sm leading-5 text-gray-600 dark:text-gray-300">
                                        {{ $mensaje }}
                                    </p>

                                    <div class="mt-3 flex items-center justify-between gap-3">
                                        @if($liga)
                                            <a
                                                href="{{ url($liga) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400 dark:hover:text-blue-300"
                                            >
                                                Ver detalle
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7m0 0H9m8 0v8" />
                                                </svg>
                                            </a>
                                        @else
                                            <span class="text-[11px] text-gray-400 dark:text-gray-500">Sin enlace adicional</span>
                                        @endif

                                        @unless($leida)
                                            <button
                                                wire:click="marcarComoLeida('{{ $notificacion->id }}')"
                                                type="button"
                                                class="inline-flex items-center rounded-full bg-blue-600 px-3 py-1.5 text-[11px] font-semibold text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400"
                                            >
                                                Marcar leida
                                            </button>
                                        @else
                                            <span class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400">Leida</span>
                                        @endunless
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @script
    <script>
        (() => {
            const userId = @js(auth()->id());

            if (!userId || !window.Echo) {
                return;
            }

            window.__mtiNotificationChannels ??= {};

            if (window.__mtiNotificationChannels[userId]) {
                return;
            }

            const channelName = `App.Models.User.${userId}`;

            window.__mtiNotificationChannels[userId] = window.Echo
                .private(channelName)
                .notification(() => {
                    Livewire.dispatch('notificacionRecibida');
                });
        })();
    </script>
    @endscript
</div>
