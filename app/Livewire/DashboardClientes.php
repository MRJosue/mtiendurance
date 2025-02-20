<?php

namespace App\Livewire;


use Livewire\Component;
use Illuminate\Support\Facades\DB;

class DashboardClientes extends Component
{
    public $clientes;

    public function mount()
    {
        // Obtener clientes desde la base de datos en cPanel
        $this->clientes = DB::connection('cpanel_mysql')->table('client')->limit(2)->get();
    }

    public function render()
    {
        return view('livewire.dashboard-clientes');
    }
}
