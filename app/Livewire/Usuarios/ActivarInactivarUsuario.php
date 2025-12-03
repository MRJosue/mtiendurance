<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use App\Models\User;
use App\Models\Proyecto;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
class ActivarInactivarUsuario extends Component
{
    public User $user;

    // Modales
    public bool $showDeactivateModal = false;
    public bool $showActivateModal = false;

    // Estadísticas para mostrar en el modal
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
            'es_propietario'          => (bool) $this->user->es_propietario,
            'total_subordinados'      => 0,
            'total_usuarios_afectados'=> 1,
            'total_proyectos'         => 0,
            'total_pedidos'           => 0,
        ];

        $this->activateStats = [
            'total_proyectos' => 0,
            'total_pedidos'   => 0,
        ];
    }

    /**
     * Abre el modal de inactivación y calcula las estadísticas.
     */
    public function openDeactivateModal(): void
    {
        $this->calcularEstadisticasInactivacion();
        $this->showDeactivateModal = true;
    }

    /**
     * Abre el modal de activación y calcula las estadísticas.
     * Solo aplica al propio usuario (no subordinados).
     */
    public function openActivateModal(): void
    {
        $this->calcularEstadisticasActivacion();
        $this->showActivateModal = true;
    }

    /**
     * Inactivar usuario:
     * - Si es propietario: inactiva usuario + subordinados + proyectos/pedidos de todos.
     * - Si NO es propietario: solo inactiva el usuario y sus proyectos/pedidos.
     */
    public function inactivarUsuario(): void
    {
        DB::transaction(function () {
            $userIds = [$this->user->id];

            if ($this->user->es_propietario) {
                // Asumimos que el propietario tiene un campo JSON 'subordinados' con IDs
                $subordinadosIds = (array) ($this->user->subordinados ?? []);
                $subordinadosIds = array_filter($subordinadosIds); // quitar null/0

                if (!empty($subordinadosIds)) {
                    $userIds = array_unique(array_merge($userIds, $subordinadosIds));
                }
            }

            // Inactivar usuarios
            User::whereIn('id', $userIds)->update(['ind_activo' => 0]);

            // Inactivar proyectos relacionados
            Proyecto::whereIn('usuario_id', $userIds)->update(['ind_activo' => 0]);

            // Inactivar pedidos relacionados
            Pedido::whereIn('user_id', $userIds)->update(['ind_activo' => 0]);
        });

        $this->user->refresh();
        $this->resetStats();
        $this->showDeactivateModal = false;

        // Notificar al front / componente padre
        $this->dispatch('usuario-actualizado', id: $this->user->id, activo: (bool) $this->user->ind_activo);
    }

    /**
     * Activar usuario:
     * - Solo se muestra si el usuario está inactivo.
     * - Solo activa ESTE usuario y sus proyectos/pedidos.
     */
    public function activarUsuario(): void
    {
        DB::transaction(function () {
            // Activar usuario actual
            $this->user->update(['ind_activo' => 1]);

            // Activar proyectos del usuario
            Proyecto::where('usuario_id', $this->user->id)->update(['ind_activo' => 1]);

            // Activar pedidos del usuario
            Pedido::where('user_id', $this->user->id)->update(['ind_activo' => 1]);
        });

        $this->user->refresh();
        $this->resetStats();
        $this->showActivateModal = false;

        $this->dispatch('usuario-actualizado', id: $this->user->id, activo: (bool) $this->user->ind_activo);
    }

    /**
     * Calcula estadísticas antes de inactivar.
     */
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

        $totalProyectos = Proyecto::whereIn('usuario_id', $userIds)->count();
        $totalPedidos   = Pedido::whereIn('user_id', $userIds)->count();

        $this->deactivateStats = [
            'es_propietario'           => (bool) $this->user->es_propietario,
            'total_subordinados'       => $totalSubordinados,
            'total_usuarios_afectados' => count($userIds),
            'total_proyectos'          => $totalProyectos,
            'total_pedidos'            => $totalPedidos,
        ];
    }

    /**
     * Calcula estadísticas antes de activar al usuario.
     * Solo considera al propio usuario (no subordinados).
     */
    protected function calcularEstadisticasActivacion(): void
    {
        $totalProyectos = Proyecto::where('usuario_id', $this->user->id)
            ->where('ind_activo', 0)
            ->count();

        $totalPedidos = Pedido::where('user_id', $this->user->id)
            ->where('ind_activo', 0)
            ->count();

        $this->activateStats = [
            'total_proyectos' => $totalProyectos,
            'total_pedidos'   => $totalPedidos,
        ];
    }

    public function render()
    {
        return view('livewire.usuarios.activar-inactivar-usuario');
    }
}



// class ActivarInactivarUsuario extends Component
// {
//     public function render()
//     {
//         return view('livewire.usuarios.activar-inactivar-usuario');
//     }
// }
