<div class="container mx-auto max-w-7xl p-4 sm:p-6">
    <div class="mb-5 rounded-3xl border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-blue-900 px-5 py-5 text-white shadow-lg">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-200">Preproyectos</p>
                <h2 class="mt-1 text-2xl font-semibold">Crear Nuevo Preproyecto</h2>
                <p class="mt-1 text-sm text-slate-200">Captura la información principal del proyecto, sus opciones, direcciones y archivos en un solo flujo.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs font-medium text-slate-100">
                <span class="rounded-full bg-white/10 px-3 py-1 backdrop-blur">Formulario guiado</span>
                <span class="rounded-full bg-white/10 px-3 py-1 backdrop-blur">Carga de archivos</span>
                <span class="rounded-full bg-white/10 px-3 py-1 backdrop-blur">Fechas automáticas</span>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm">
            {{ session('message') }}
        </div>
    @elseif (session()->has('error'))
        <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="create" class="space-y-5">
        @include('livewire.preproyectos.partials.create-pre-project.general-data')
        @include('livewire.preproyectos.partials.create-pre-project.features-options')
        @include('livewire.preproyectos.partials.create-pre-project.sizes-quantities')
        @include('livewire.preproyectos.partials.create-pre-project.support-files')
        @include('livewire.preproyectos.partials.create-pre-project.addresses-dates')

        <button type="submit" class="inline-flex items-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
            Crear Preproyecto
        </button>
    </form>

    @include('livewire.preproyectos.partials.create-pre-project.modal-client')
    @include('livewire.preproyectos.partials.create-pre-project.modal-address')
    @include('livewire.preproyectos.partials.create-pre-project.scripts')

    {{-- @if ($producto_id)
        <livewire:visuales.layout-render :producto_id="$producto_id" />
    @endif --}}
</div>
