<?php

namespace App\Livewire\Produccion\Muestras;


use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use App\Models\Pedido;

class AdminMuestrasTabs extends Component
{
    #[Url(as: 'tab')]
    public string $tab = 'PENDIENTE';

    protected array $tabPermissions = [
        'PENDIENTE'     => 'asideAdministraciónMuestrasTabPendiente',
        'SOLICITADA'    => 'asideAdministraciónMuestrasTabSoliocitada',
        'MUESTRA LISTA' => 'asideAdministraciónMuestrasTabMuestraLista',
        'ENTREGADA'     => 'asideAdministraciónMuestrasTabEntregada',
        'CANCELADA'     => 'asideAdministraciónMuestrasTabCancelada',
    ];

    public array $counts = [
        'PENDIENTE'      => 0,
        'SOLICITADA'     => 0,
        'MUESTRA LISTA'  => 0,
        'ENTREGADA'      => 0,
        'CANCELADA'      => 0,
    ];

    public function mount(): void
    {
        $this->loadCounts();
        $this->normalizeActiveTab();
    }

    #[On('muestraActualizada')]
    public function loadCounts(): void
    {
        $rows = Pedido::deMuestra()
            ->selectRaw('estatus_muestra, COUNT(*) as total')
            ->groupBy('estatus_muestra')
            ->pluck('total', 'estatus_muestra')
            ->toArray();

        foreach ($this->counts as $k => $v) {
            $this->counts[$k] = (int)($rows[$k] ?? 0);
        }

        $this->normalizeActiveTab();
    }

    public function setTab(string $tab): void
    {
        $requestedTab = strtoupper($tab);

        if (!in_array($requestedTab, $this->getVisibleTabs(), true)) {
            $this->normalizeActiveTab();
            return;
        }

        $this->tab = $requestedTab;
        $this->dispatch('tabsChanged');
    }

    public function getVisibleTabs(): array
    {
        $user = auth()->user();

        return collect(array_keys($this->counts))
            ->filter(function (string $tab) use ($user) {
                $permission = $this->tabPermissions[$tab] ?? null;

                return $permission && $user?->can($permission);
            })
            ->values()
            ->all();
    }

    protected function normalizeActiveTab(): void
    {
        $visibleTabs = $this->getVisibleTabs();

        if (empty($visibleTabs)) {
            $this->tab = 'PENDIENTE';
            return;
        }

        $currentTab = strtoupper($this->tab);

        if (!array_key_exists($currentTab, $this->counts) || !in_array($currentTab, $visibleTabs, true)) {
            $this->tab = $visibleTabs[0];
        }
    }

    public function render()
    {
        return view('livewire.produccion.muestras.admin-muestras-tabs');
    }
}


// class AdminMuestrasTabs extends Component
// {
//     public function render()
//     {
//         return view('livewire.produccion.muestras.admin-muestras-tabs');
//     }
// }
