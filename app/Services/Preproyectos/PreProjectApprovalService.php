<?php

namespace App\Services\Preproyectos;

use App\Models\ArchivoProyecto;
use App\Models\PreProyecto;
use App\Models\Proyecto;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PreProjectApprovalService
{
    public function approve(int $preProyectoId, int $approvedByUserId): Proyecto
    {
        try {
            return DB::transaction(function () use ($preProyectoId, $approvedByUserId) {
                $preProyecto = PreProyecto::query()
                    ->whereKey($preProyectoId)
                    ->lockForUpdate()
                    ->first();

                if (!$preProyecto) {
                    $existingApproval = DB::table('pre_proyecto_aprobaciones')
                        ->where('pre_proyecto_id', $preProyectoId)
                        ->first();

                    if ($existingApproval?->proyecto_id) {
                        throw new RuntimeException(
                            "Este preproyecto ya fue preaprobado anteriormente como proyecto #{$existingApproval->proyecto_id}."
                        );
                    }

                    throw new RuntimeException('El preproyecto ya no existe o ya fue procesado por otro usuario.');
                }

                $existingApproval = DB::table('pre_proyecto_aprobaciones')
                    ->where('pre_proyecto_id', $preProyectoId)
                    ->lockForUpdate()
                    ->first();

                if ($existingApproval?->proyecto_id) {
                    throw new RuntimeException(
                        "Este preproyecto ya fue preaprobado anteriormente como proyecto #{$existingApproval->proyecto_id}."
                    );
                }

                if (!$existingApproval) {
                    DB::table('pre_proyecto_aprobaciones')->insert([
                        'pre_proyecto_id' => $preProyectoId,
                        'estado' => 'PROCESSING',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $lockedFiles = ArchivoProyecto::query()
                    ->where('pre_proyecto_id', $preProyectoId)
                    ->lockForUpdate()
                    ->get();

                if ($lockedFiles->where('flag_descarga', 0)->count() > 0) {
                    throw new RuntimeException('Debes descargar todos los archivos antes de aprobar.');
                }

                $proyecto = $preProyecto->transferirAProyecto();

                DB::table('pre_proyecto_aprobaciones')
                    ->where('pre_proyecto_id', $preProyectoId)
                    ->update([
                        'proyecto_id' => $proyecto->id,
                        'aprobado_por_id' => $approvedByUserId,
                        'estado' => 'COMPLETED',
                        'error' => null,
                        'aprobado_at' => now(),
                        'updated_at' => now(),
                    ]);

                return $proyecto;
            }, 5);
        } catch (Throwable $e) {
            $existingApproval = DB::table('pre_proyecto_aprobaciones')
                ->where('pre_proyecto_id', $preProyectoId)
                ->first();

            if (!($existingApproval?->estado === 'COMPLETED' && $existingApproval?->proyecto_id)) {
                DB::table('pre_proyecto_aprobaciones')->updateOrInsert(
                    ['pre_proyecto_id' => $preProyectoId],
                    [
                        'estado' => 'FAILED',
                        'error' => mb_substr($e->getMessage(), 0, 65535),
                        'aprobado_por_id' => $approvedByUserId,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            throw $e;
        }
    }
}

