<?php

namespace App\Livewire;

use Livewire\Component;


class AsideMenu extends Component
{
    public bool $sidebarOpen;

    public array $openSections = [];
    public ?string $selectedRoute = null;

    public function mount()
    {
        $this->sidebarOpen = request()->wantsSidebar ?? (request()->user() ? true : false); 
        $this->selectedRoute = request()->route()->getName();
        // Opcional: carga estados guardados en base de datos o cache si quieres persistir
        $this->openSections = session('aside_open_sections', []);
    }

    public function toggleSidebar()
    {
        $this->sidebarOpen = !$this->sidebarOpen;
    }

    public function toggleSection(string $name)
    {
        if (isset($this->openSections[$name])) {
            unset($this->openSections[$name]);
        } else {
            $this->openSections[$name] = true;
        }
        session(['aside_open_sections' => $this->openSections]);
    }

    public function setSelected(string $route)
    {
        $this->selectedRoute = $route;
    }

    public function isActive(string $route): bool
    {
        return $this->selectedRoute === $route;
    }

    public function render()
    {
        return view('livewire.aside-menu');
    }
}

// class AsideMenu extends Component
// {
//     public function render()
//     {
//         return view('livewire.aside-menu');
//     }
// }
