<?php

namespace App\Livewire\Catalogos;

use App\Models\Estado;
use App\Models\Pais;
use App\Models\TipoEnvio;
use Livewire\Component;
use Livewire\WithPagination;

class CrudEstado extends Component
{
    use WithPagination;

    // Form
    public string $nombre = '';
    public ?int $pais_id = null;
    public ?int $estadoId = null;

    // Tipos de envío seleccionados (checkboxes)
    public array $selectedTiposEnvio = [];

    // UI
    public bool $isEditMode = false;
    public bool $showModal  = false;

    // Table
    public int $perPage = 10;
    public array $perPageOptions = [10, 20, 30, 50, 100];

    // Filtros por columna (excepto tipos de envío)
    public array $filters = [
        'id'      => null,   // acepta "1" o "1,2,3"
        'nombre'  => null,   // like
        'pais_id' => null,   // select
    ];

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'pais_id' => 'required|exists:paises,id',
        'selectedTiposEnvio' => 'array',
        'selectedTiposEnvio.*' => 'integer|exists:tipo_envio,id',
    ];

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filters = [
            'id'      => null,
            'nombre'  => null,
            'pais_id' => null,
        ];

        $this->resetPage();
        $this->dispatch('filters-cleared'); // por si quieres escuchar esto en Alpine
    }

    public function render()
    {
        $query = Estado::query()->with(['pais', 'tipoEnvios']);

        // ----- filtros -----
        $query
            ->when($this->filters['id'], function ($q, $v) {
                $ids = collect(preg_split('/[,;\s]+/', (string) $v, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn ($i) => (int) trim($i))
                    ->filter(fn ($i) => $i > 0)
                    ->values();

                if ($ids->isNotEmpty()) {
                    $q->whereIn('id', $ids->all());
                }
            })
            ->when($this->filters['nombre'], function ($q, $v) {
                $v = trim((string) $v);
                $q->where('nombre', 'like', "%{$v}%");
            })
            ->when($this->filters['pais_id'], function ($q, $v) {
                $q->where('pais_id', (int) $v);
            });

        return view('livewire.catalogos.crud-estado', [
            'estados' => $query->orderBy('id', 'desc')->paginate($this->perPage),
            'paises' => Pais::orderBy('nombre')->get(),
            'tiposEnvio' => TipoEnvio::orderBy('nombre')->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $estado = Estado::with('tipoEnvios')->findOrFail($id);

        $this->estadoId = $estado->id;
        $this->nombre   = (string) $estado->nombre;
        $this->pais_id  = (int) $estado->pais_id;

        $this->selectedTiposEnvio = $estado->tipoEnvios
            ->pluck('id')
            ->map(fn($v) => (int) $v)
            ->all();

        $this->isEditMode = true;
        $this->showModal  = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function resetFields(): void
    {
        $this->resetValidation();
        $this->nombre = '';
        $this->pais_id = null;
        $this->estadoId = null;
        $this->selectedTiposEnvio = [];
        $this->isEditMode = false;
    }

    public function store(): void
    {
        $this->validate();

        $estado = Estado::create([
            'nombre' => $this->nombre,
            'pais_id' => $this->pais_id,
        ]);

        $estado->tipoEnvios()->sync($this->selectedTiposEnvio);

        session()->flash('message', 'Estado creado exitosamente.');

        $this->closeModal();
        $this->resetFields();
    }

    public function update(): void
    {
        $this->validate();

        $estado = Estado::findOrFail($this->estadoId);

        $estado->update([
            'nombre' => $this->nombre,
            'pais_id' => $this->pais_id,
        ]);

        $estado->tipoEnvios()->sync($this->selectedTiposEnvio);

        session()->flash('message', 'Estado actualizado exitosamente.');

        $this->closeModal();
        $this->resetFields();
    }

    public function delete(int $id): void
    {
        $estado = Estado::findOrFail($id);

        $estado->tipoEnvios()->sync([]);
        $estado->delete();

        session()->flash('message', 'Estado eliminado exitosamente.');
    }
}