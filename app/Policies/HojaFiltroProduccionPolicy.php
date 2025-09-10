<?php

namespace App\Policies;

use App\Models\User;
use App\Models\HojaFiltroProduccion;
use Illuminate\Auth\Access\Response;

class HojaFiltroProduccionPolicy
{
    /**
     * Ver listado (no lo usamos directo, pero útil si quieres proteger el índice).
     */
    public function viewAny(User $user): bool
    {
        // ver el índice solo para usuarios autenticados
        return $user !== null;
    }

    /**
     * Ver una hoja en particular (ruta /produccion/hojas/{slug}).
     * Regla:
     * - Debe estar visible.
     * - Si tiene role_id, el usuario debe tener ese rol.
     * - Bypass: rol ADMINISTRACION (ajústalo a tu rol admin si es otro).
     */
    public function view(User $user, HojaFiltroProduccion $hoja): bool
    {
        if (!$hoja->visible) {
            return false;
        }

        // bypass de administrador (opcional)
        if ($user->hasRole('ADMINISTRACION')) {
            return true;
        }

        // sin restricción por rol
        if (empty($hoja->role_id)) {
            return true;
        }

        

        // requiere rol específico
        return $user->roles()->where('id', $hoja->role_id)->exists();
    }

    /**
     * Habilidad "manage" para CRUD de hojas.
     * La usamos en las rutas del CRUD con ->can('manage', HojaFiltroProduccion::class).
     * Ajusta la condición a tu necesidad.
     */
    public function manage(User $user): bool
    {
        // Por ejemplo, solo el rol ADMINISTRACION puede crear/editar.
        return $user->hasRole('ADMINISTRACION');
    }

    // Si quieres granularidad clásica:
    public function create(User $user): bool { return $this->manage($user); }
    public function update(User $user, HojaFiltroProduccion $hoja): bool { return $this->manage($user); }
    public function delete(User $user, HojaFiltroProduccion $hoja): bool { return $this->manage($user); }

    

}