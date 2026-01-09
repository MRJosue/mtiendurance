<?php

namespace App\Services;

use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ProyectoAccessService
{
    public function puedeVer(int $userId, int $proyectoId): bool
    {
        $user = User::query()->select(['id','tipo','empresa_id','sucursal_id','es_propietario'])->find($userId);
        if (!$user) return false;

        $proyecto = Proyecto::query()
            ->with(['user:id,empresa_id,sucursal_id', 'user.sucursal:id,empresa_id'])
            ->select(['id','usuario_id'])
            ->find($proyectoId);

        if (!$proyecto) return false;

        // Reutiliza exactamente la policy:
        return Gate::forUser($user)->allows('view', $proyecto);
    }
}
