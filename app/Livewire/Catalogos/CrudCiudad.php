<?php

namespace App\Livewire\Catalogos;

use App\Models\Ciudad;
use App\Models\Estado;
use App\Models\Pais;
use App\Models\TipoEnvio;
use Livewire\Component;
use Livewire\WithPagination;

class CrudCiudad extends Component
{
    use WithPagination;

    // Form
    public string $nombre = '';
    public ?int $estado_id = null;
    public array $selectedTiposEnvio = [];
    public ?int $ciudadId = null;

    // UI
    public bool $isEditMode = false;
    public bool $showModal  = false;

    // Table
    public int $perPage = 10;
    public array $perPageOptions = [10, 20, 30, 50, 100];

    public array $filters = [
        'id'            => null,
        'nombre'        => null,
        'pais_id'       => null, // ✅ NUEVO
        'estado_id'     => null,
        'tipo_envio_id' => null,
    ];

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'estado_id' => 'required|exists:estados,id',
        'selectedTiposEnvio' => 'array',
        'selectedTiposEnvio.*' => 'exists:tipo_envio,id',
    ];

    public function updatedPerPage(): void
    {
        if (!in_array((int)$this->perPage, $this->perPageOptions, true)) {
            $this->perPage = 10;
        }
        $this->resetPage();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filters = [
            'id'            => null,
            'nombre'        => null,
            'pais_id'       => null, // ✅ NUEVO
            'estado_id'     => null,
            'tipo_envio_id' => null,
        ];
        $this->resetPage();
        $this->dispatch('filters-cleared');
    }

    public function openCreateModal(): void
    {
        $this->resetFields();
        $this->isEditMode = false;
        $this->showModal  = true;
        $this->resetErrorBag();
    }

    public function openEditModal(int $id): void
    {
        $ciudad = Ciudad::with('tipoEnvios')->findOrFail($id);

        $this->ciudadId = $ciudad->id;
        $this->nombre   = (string) $ciudad->nombre;
        $this->estado_id = (int) $ciudad->estado_id;

        $this->selectedTiposEnvio = $ciudad->tipoEnvios
            ->pluck('id')
            ->map(fn($x) => (int)$x)
            ->values()
            ->all();

        $this->isEditMode = true;
        $this->showModal  = true;
        $this->resetErrorBag();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function resetFields(): void
    {
        $this->nombre = '';
        $this->estado_id = null;
        $this->selectedTiposEnvio = [];
        $this->ciudadId = null;
        $this->isEditMode = false;
    }

    public function store(): void
    {
        $this->validate();

        $ciudad = Ciudad::create([
            'nombre' => $this->nombre,
            'estado_id' => $this->estado_id,
        ]);

        $ciudad->syncTiposEnvio($this->selectedTiposEnvio);

        session()->flash('message', 'Ciudad creada exitosamente.');

        $this->closeModal();
        $this->resetFields();
    }

    public function update(): void
    {
        $this->validate();

        $ciudad = Ciudad::findOrFail($this->ciudadId);

        $ciudad->update([
            'nombre' => $this->nombre,
            'estado_id' => $this->estado_id,
        ]);

        $ciudad->syncTiposEnvio($this->selectedTiposEnvio);

        session()->flash('message', 'Ciudad actualizada exitosamente.');

        $this->closeModal();
        $this->resetFields();
    }

    public function deleteCiudad(int $id): void
    {
        Ciudad::findOrFail($id)->delete();
        session()->flash('message', 'Ciudad eliminada exitosamente.');
        $this->resetPage();
    }

    public function render()
    {
        // ✅ Cargar país vía estado.pais
        $query = Ciudad::query()->with(['estado.pais', 'tipoEnvios']);

        // Filtro ID (soporta lista)
        $query->when($this->filters['id'], function ($q, $v) {
            $ids = collect(preg_split('/[,;\s]+/', (string)$v, -1, PREG_SPLIT_NO_EMPTY))
                ->map(fn($i) => (int)trim($i))
                ->filter(fn($i) => $i > 0)
                ->values();

            if ($ids->count() === 1) $q->where('id', $ids->first());
            elseif ($ids->isNotEmpty()) $q->whereIn('id', $ids->all());
        });

        // Nombre
        $query->when($this->filters['nombre'], fn($q, $v) =>
            $q->where('nombre', 'like', '%'.trim((string)$v).'%')
        );

        // ✅ País (filtra por pais_id en la tabla estados)
        $query->when($this->filters['pais_id'], function ($q, $v) {
            $paisId = (int) $v;
            $q->whereHas('estado', fn($sub) => $sub->where('pais_id', $paisId));
        });

        // Estado
        $query->when($this->filters['estado_id'], fn($q, $v) =>
            $q->where('estado_id', (int)$v)
        );

        // Tipo Envío
        $query->when($this->filters['tipo_envio_id'], function ($q, $v) {
            $tipoId = (int) $v;
            $q->whereHas('tipoEnvios', fn($sub) => $sub->where('tipo_envio.id', $tipoId));
        });

        $ciudades = $query->orderByDesc('id')->paginate($this->perPage);

        return view('livewire.catalogos.crud-ciudad', [
            'ciudades'   => $ciudades,
            'paises'     => Pais::orderBy('nombre')->get(),   // ✅ NUEVO
            'estados'    => Estado::orderBy('nombre')->get(),
            'tiposEnvio' => TipoEnvio::orderBy('nombre')->get(),
        ]);
    }
}