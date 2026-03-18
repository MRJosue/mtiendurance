<?php

namespace App\Livewire\Proyectos;

use App\Models\proyecto_estados;
use Livewire\Component;
use App\Models\Proyecto;
use App\Models\Tarea;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NuevaNotificacion;

class TareasDiseno extends Component
{
    public Proyecto $proyecto;
    public bool $modalOpen = false;
    public bool $modalVerMas = false;
    public ?Proyecto $proyectoSeleccionado = null;

    public ?int $selectedUser = null;
    public string $taskDescription = '';
    public string $taskType = '';

    public bool $modalProveedorOpen = false;
    public ?int $selectedProveedor = null;

    protected function rules(): array
    {
        return [
            'selectedUser' => 'required|exists:users,id',
            'taskDescription' => 'required|min:5',
            'taskType' => 'required|in:DISEÑO,PRODUCCION,CORTE,PINTURA,FACTURACION,INDEFINIDA',
        ];
    }

    public function mount(int $proyectoId): void
    {
        $this->proyecto = Proyecto::with(['tareas.staff', 'estados.usuario', 'proveedor'])
            ->findOrFail($proyectoId);
    }

    public function abrirModal(): void
    {
        $this->proyecto->refresh();

        if (strtoupper(trim((string) $this->proyecto->estado)) === 'DISEÑO APROBADO') {
            session()->flash('message', 'No es posible crear tareas porque el proyecto ya está en DISEÑO APROBADO.');
            return;
        }

        $this->selectedUser = null;
        $this->taskDescription = '';
        $this->taskType = '';
        $this->modalOpen = true;
        $this->resetErrorBag();
    }

    public function cerrarModal(): void
    {
        $this->reset(['modalOpen', 'selectedUser', 'taskDescription', 'taskType']);
        $this->resetErrorBag();
    }

    public function asignarTarea(): void
    {

        $this->proyecto->refresh();

        if (strtoupper(trim((string) $this->proyecto->estado)) === 'DISEÑO APROBADO') {
            $this->addError('taskType', 'No es posible crear tareas porque el proyecto ya está en DISEÑO APROBADO.');
            return;
        }

        $this->validate();

        $existe = Tarea::where('proyecto_id', $this->proyecto->id)
            ->whereIn('estado', ['PENDIENTE', 'EN PROCESO'])
            ->exists();

        if ($existe) {
            $this->addError(
                'selectedUser',
                'Ya existe una tarea activa en este proyecto.'
            );
            return;
        }

        Tarea::create([
            'proyecto_id' => $this->proyecto->id,
            'staff_id' => $this->selectedUser,
            'descripcion' => $this->taskDescription,
            'tipo' => $this->taskType,
            'estado' => 'PENDIENTE',
        ]);

        $this->proyecto->update(['estado' => 'ASIGNADO']);

        proyecto_estados::create([
            'proyecto_id' => $this->proyecto->id,
            'estado' => 'Proyecto asignado a diseñador',
            'fecha_inicio' => now(),
            'usuario_id' => Auth::id(),
        ]);

        $ruta = 'proyectos/' . $this->proyecto->id;

        $this->enviarNotificacion(
            Auth::id(),
            'Asignaste la tarea del proyecto ' . $this->proyecto->id,
            $ruta
        );

        $this->enviarNotificacion(
            $this->selectedUser,
            'Tienes asignada una tarea del proyecto ID: ' . $this->proyecto->id,
            $ruta
        );

        $this->enviarNotificacion(
            $this->proyecto->usuario_id,
            'Cambio de estatus en proyecto: ' . $this->proyecto->id,
            $ruta
        );

        session()->flash('message', 'Tarea asignada exitosamente.');
        $this->dispatch('tareaAsignada');
        $this->cerrarModal();

        $this->proyecto->refresh()->load(['tareas.staff', 'estados.usuario', 'proveedor']);
    }

    public function verMas(): void
    {
        $this->proyectoSeleccionado = Proyecto::with('estados.usuario')
            ->findOrFail($this->proyecto->id);

        $this->modalVerMas = true;
    }

    public function cerrarModalVerMas(): void
    {
        $this->modalVerMas = false;
        $this->proyectoSeleccionado = null;
    }

    protected function enviarNotificacion(?int $userId, string $mensaje, ?string $ruta = null): void
    {
        if ($user = User::find($userId)) {
            $liga = $ruta ? config('app.url') . '/' . $ruta : null;
            $user->notify(new NuevaNotificacion($mensaje, $liga));
        }
    }

    public function abrirModalProveedor(): void
    {
        $this->modalProveedorOpen = true;
        $this->resetErrorBag();
    }

    public function cerrarModalProveedor(): void
    {
        $this->reset(['modalProveedorOpen', 'selectedProveedor']);
        $this->resetErrorBag();
    }

    public function asignarProveedor(): void
    {
        $this->validate([
            'selectedProveedor' => 'required|exists:users,id',
        ], [
            'selectedProveedor.required' => 'Selecciona un proveedor.',
            'selectedProveedor.exists' => 'El proveedor seleccionado no existe.',
        ]);

        $this->proyecto->proveedor_id = $this->selectedProveedor;
        $this->proyecto->save();

        Chat::firstOrCreate(
            [
                'proyecto_id' => $this->proyecto->id,
                'tipo_chat' => 2,
                'proveedor_id' => $this->selectedProveedor,
            ],
            [
                'fecha_creacion' => now(),
            ]
        );

        proyecto_estados::create([
            'proyecto_id' => $this->proyecto->id,
            'estado' => 'Proveedor asignado al proyecto',
            'fecha_inicio' => now(),
            'usuario_id' => Auth::id(),
        ]);

        $ruta = 'proyectos/' . $this->proyecto->id;

        $this->enviarNotificacion(
            Auth::id(),
            'Asignaste un proveedor al proyecto ' . $this->proyecto->id,
            $ruta
        );

        $this->enviarNotificacion(
            $this->selectedProveedor,
            'Te han asignado como proveedor del proyecto ' . $this->proyecto->id,
            $ruta
        );

        $this->enviarNotificacion(
            $this->proyecto->usuario_id,
            'Se asignó un proveedor al proyecto ' . $this->proyecto->id,
            $ruta
        );

        session()->flash('message', 'Proveedor asignado y chat de proveedor creado correctamente.');

        $this->cerrarModalProveedor();
        $this->proyecto->refresh()->load(['tareas.staff', 'estados.usuario', 'proveedor']);
    }

    public function render()
    {
        $this->proyecto->load(['tareas.staff', 'estados.usuario', 'proveedor']);

        $staffUsers = User::with('roles')
            ->where('tipo', 3)
            ->where('ind_activo', 1)
            ->orderBy('name')
            ->get();

        $proveedores = User::whereHas('roles', function ($q) {
            $q->where('name', 'proveedor');
        })->get();

        return view('livewire.proyectos.tareas-diseno', [
            'staffUsers' => $staffUsers,
            'proveedores' => $proveedores,
        ]);
    }
}