<?php


namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Log;



class ConfiguracionesUsuario extends Component
{
    public int $userId;

    public bool $flag_user_sel_preproyectos = false;
    public bool $flag_can_user_sel_preproyectos = false;
    public array $usuariosSeleccionados = [];

    public function getTodosLosUsuariosProperty()
    {
        return User::query()
            ->select('id', 'name')
            ->whereJsonContains('config->flag-user-sel-preproyectos', true)
            ->orderBy('name')
            ->get();
    }

    public function mount(int $userId)
    {
        $this->userId = $userId;

        // Obtener los IDs permitidos del usuario (puede venir como JSON o array)
        $idsPermitidos = User::query()
            ->where('id', $userId)
            ->value('user_can_sel_preproyectos') ?? [];

        if (!is_array($idsPermitidos)) {
            $idsPermitidos = json_decode($idsPermitidos, true) ?? [];
        }

        // Filtrar por usuarios válidos (flag-user-sel-preproyectos = true)
        $this->usuariosSeleccionados = User::query()
            ->whereIn('id', $idsPermitidos)
            ->whereJsonContains('config->flag-user-sel-preproyectos', true)
            ->pluck('id')
            ->map(fn ($v) => (int)$v)
            ->toArray();

        Log::debug('mount usuariosSeleccionados filtrados', ['data' => $this->usuariosSeleccionados]);
        Log::debug('mount UserID', ['data' => $this->userId]);

        $this->loadFlags();
    }

    public function guardarFlag(string $key, $valor): void
    {
        $user = User::findOrFail($this->userId);
        $user->setFlag($key, filter_var($valor, FILTER_VALIDATE_BOOLEAN));
    }

    public function loadFlags(): void
    {
        $user = User::findOrFail($this->userId);
        $this->flag_user_sel_preproyectos = $user->getFlag('flag-user-sel-preproyectos');
        $this->flag_can_user_sel_preproyectos = $user->getFlag('flag-can-user-sel-preproyectos');
    }

    public function updated($property): void
    {
        $user = User::findOrFail($this->userId);

        if ($property === 'flag_user_sel_preproyectos') {
            $user->setFlag('flag-user-sel-preproyectos', $this->flag_user_sel_preproyectos);
        }

        if ($property === 'flag_can_user_sel_preproyectos') {
            $user->setFlag('flag-can-user-sel-preproyectos', $this->flag_can_user_sel_preproyectos);
        }
    }

    /**
     * IDs válidos con flag-user-sel-preproyectos = true
     */
    protected function allowedUserIds(): array
    {
        return User::query()
            ->whereJsonContains('config->flag-user-sel-preproyectos', true)
            ->pluck('id')
            ->map(fn ($v) => (int)$v)
            ->toArray();
    }

    public function guardarUsuariosPermitidos()
    {
        $user = User::findOrFail($this->userId);

        // Normalizar: enteros, sin nulos, únicos
        $seleccion = collect($this->usuariosSeleccionados)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->map(fn ($v) => (int)$v)
            ->unique()
            ->values();

        $permitidos = $this->allowedUserIds();

        // Validar: mantener solo IDs válidos
        $limpios = $seleccion->filter(fn ($id) => in_array($id, $permitidos, true))->values()->all();

        Log::debug('usuariosSeleccionados (input)', ['data' => $this->usuariosSeleccionados]);
        Log::debug('usuariosSeleccionados (limpios)', ['data' => $limpios]);
        Log::debug('UserID', ['data' => $this->userId]);

        $user->update([
            'user_can_sel_preproyectos' => $limpios, // JSON cast en el modelo recomendado
        ]);

        // Notificación visual (browser event)
        $this->dispatch('notify', message: 'Usuarios asignados correctamente.', type: 'success');

        // También flash por si recargas
        session()->flash('message', 'Usuarios asignados correctamente.');
    }

    public function render()
    {
        return view('livewire.configuraciones-usuario', [
            'todosLosUsuarios' => $this->todosLosUsuarios,
        ]);
    }
}