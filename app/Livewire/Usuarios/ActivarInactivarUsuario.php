<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ActivarInactivarUsuario extends Component
{
    public User $user;

    public bool $showDeactivateModal = false;
    public bool $showActivateModal = false;

    public array $deactivateStats = [];
    public array $activateStats   = [];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->resetStats();
    }

    protected function resetStats(): void
    {
        $this->deactivateStats = [
            'es_propietario'           => (bool) $this->user->es_propietario,
            'total_subordinados'       => 0,
            'total_usuarios_afectados' => 1,
        ];

        $this->activateStats = [
            'solo_usuario' => true,
        ];
    }

    public function openDeactivateModal(): void
    {
        $this->calcularEstadisticasInactivacion();
        $this->showDeactivateModal = true;
    }

    public function openActivateModal(): void
    {
        $this->calcularEstadisticasActivacion();
        $this->showActivateModal = true;
    }

    /**
     * Inactivar usuario:
     * - Si es propietario: inactiva usuario + subordinados (solo usuarios).
     * - Si NO es propietario: solo inactiva el usuario.
     */
    public function inactivarUsuario(): void
    {
        DB::transaction(function () {
            $userIds = [$this->user->id];

            if ($this->user->es_propietario) {
                $subordinadosIds = (array) ($this->user->subordinados ?? []);
                $subordinadosIds = array_filter($subordinadosIds);

                if (!empty($subordinadosIds)) {
                    $userIds = array_unique(array_merge($userIds, $subordinadosIds));
                }
            }

            User::whereIn('id', $userIds)->update(['ind_activo' => 0]);
        });

        $this->user->refresh();
        $this->resetStats();
        $this->showDeactivateModal = false;

        $this->dispatch('usuario-actualizado', id: $this->user->id, activo: (bool) $this->user->ind_activo);
    }

    /**
     * Activar usuario:
     * - Solo activa ESTE usuario (no subordinados, no proyectos/pedidos).
     */
    public function activarUsuario(): void
    {
        DB::transaction(function () {
            $this->user->update(['ind_activo' => 1]);
        });

        $this->user->refresh();
        $this->resetStats();
        $this->showActivateModal = false;

        $this->dispatch('usuario-actualizado', id: $this->user->id, activo: (bool) $this->user->ind_activo);
    }

    protected function calcularEstadisticasInactivacion(): void
    {
        $userIds = [$this->user->id];
        $totalSubordinados = 0;

        if ($this->user->es_propietario) {
            $subordinadosIds = (array) ($this->user->subordinados ?? []);
            $subordinadosIds = array_filter($subordinadosIds);

            $totalSubordinados = count($subordinadosIds);

            if ($totalSubordinados > 0) {
                $userIds = array_unique(array_merge($userIds, $subordinadosIds));
            }
        }

        $this->deactivateStats = [
            'es_propietario'           => (bool) $this->user->es_propietario,
            'total_subordinados'       => $totalSubordinados,
            'total_usuarios_afectados' => count($userIds),
        ];
    }

    protected function calcularEstadisticasActivacion(): void
    {
        $this->activateStats = [
            'solo_usuario' => true,
        ];
    }

    public function render()
    {
        return view('livewire.usuarios.activar-inactivar-usuario');
    }
}
