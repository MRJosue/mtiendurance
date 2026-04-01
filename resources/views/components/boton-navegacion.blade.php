<div
    x-data="navigationButtons()"
    x-init="init()"
    class="fixed bottom-4 right-4 z-50 flex flex-wrap justify-end gap-2"
>
    <button
        type="button"
        @click="goBack()"
        :disabled="!canGoBack"
        class="flex items-center gap-2 rounded-lg border border-stone-300 bg-stone-100 px-4 py-2 text-stone-800 shadow-lg transition hover:bg-stone-200 disabled:cursor-not-allowed disabled:opacity-50 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100 dark:hover:bg-stone-700"
        aria-label="Regresar"
        title="Regresar"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-stone-600 dark:text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Regresar
    </button>

    <button
        type="button"
        @click="goForward()"
        :disabled="!canGoForward"
        class="flex items-center gap-2 rounded-lg border border-stone-300 bg-stone-100 px-4 py-2 text-stone-800 shadow-lg transition hover:bg-stone-200 disabled:cursor-not-allowed disabled:opacity-50 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100 dark:hover:bg-stone-700"
        aria-label="Avanzar"
        title="Avanzar"
    >
        Avanzar
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-stone-600 dark:text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </button>
</div>

<script>
    function navigationButtons() {
        return {
            canGoBack: false,
            canGoForward: false,
            refreshHandler: null,

            init() {
                this.refreshHandler = () => this.refreshState();

                window.addEventListener('popstate', this.refreshHandler);
                window.addEventListener('pageshow', this.refreshHandler);
                document.addEventListener('livewire:navigated', this.refreshHandler);

                if ('navigation' in window && typeof window.navigation.addEventListener === 'function') {
                    window.navigation.addEventListener('currententrychange', this.refreshHandler);
                }

                this.refreshState();
            },

            refreshState() {
                this.canGoBack = this.detectBackAvailability();
                this.canGoForward = this.detectForwardAvailability();
            },

            detectBackAvailability() {
                if ('navigation' in window && typeof window.navigation.canGoBack === 'boolean') {
                    return window.navigation.canGoBack;
                }

                return window.history.length > 1 || this.hasSameOriginReferrer();
            },

            detectForwardAvailability() {
                if ('navigation' in window && typeof window.navigation.canGoForward === 'boolean') {
                    return window.navigation.canGoForward;
                }

                return false;
            },

            hasSameOriginReferrer() {
                if (!document.referrer) {
                    return false;
                }

                try {
                    return new URL(document.referrer).origin === window.location.origin;
                } catch (error) {
                    return false;
                }
            },

            goBack() {
                if (!this.canGoBack) {
                    return;
                }

                if ('navigation' in window && typeof window.navigation.back === 'function') {
                    window.navigation.back().catch(() => window.history.back());
                    return;
                }

                window.history.back();
            },

            goForward() {
                if (!this.canGoForward) {
                    return;
                }

                if ('navigation' in window && typeof window.navigation.forward === 'function') {
                    window.navigation.forward().catch(() => window.history.forward());
                    return;
                }

                window.history.forward();
            },
        };
    }
</script>
