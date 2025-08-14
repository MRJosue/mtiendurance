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

        if (!array_key_exists(strtoupper($this->tab), $this->counts)) {
            $this->tab = 'PENDIENTE';
        }
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
    }

    public function setTab(string $tab): void
    {
        $this->tab = strtoupper($tab);
        $this->dispatch('tabsChanged');
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
