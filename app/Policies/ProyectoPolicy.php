<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Proyecto;


class ProyectoPolicy
{
    private function esStaffOAdmin(User $user): bool
    {
        // Regla principal: si es ADMIN o STAFF => acceso total
        return $user->esTipoUsuario('ADMIN') || $user->esTipoUsuario('STAFF');
    }

    public function view(User $user, Proyecto $proyecto): bool
    {
        // Admin/Staff: todo
        if ($this->esStaffOAdmin($user)) {
            return true;
        }

        // Cliente: validar dueño/subordinado
        if ($user->esTipoUsuario('CLIENTE')) {

            // Subordinado: solo sus proyectos
            if (!$user->es_propietario) {
                return (int)$proyecto->usuario_id === (int)$user->id;
            }

            // Dueño: sus proyectos + proyectos de subordinados de su empresa
            if ((int)$proyecto->usuario_id === (int)$user->id) {
                return true;
            }

            $duenoEmpresaId = (int) ($user->empresa_id ?? 0);
            if ($duenoEmpresaId <= 0) {
                return false;
            }

            // El proyecto es de otro usuario: permitir si ese usuario pertenece a la misma empresa
            // (ya sea porque es principal con empresa_id, o subordinado vía sucursal->empresa_id)
            $proyectoUser = $proyecto->user;

            $empresaDelUsuarioDelProyecto =
                (int) ($proyectoUser?->empresa_id ?? 0)
                ?: (int) ($proyectoUser?->sucursal?->empresa_id ?? 0);

            return $empresaDelUsuarioDelProyecto === $duenoEmpresaId;
        }

        // Otros tipos (proveedor, etc): no definido aquí => sin acceso
        return false;
    }
}