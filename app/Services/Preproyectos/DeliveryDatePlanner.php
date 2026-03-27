<?php

namespace App\Services\Preproyectos;

use App\Models\Producto;
use App\Models\TipoEnvio;
use Carbon\Carbon;

class DeliveryDatePlanner
{
    public function calculate(?string $fechaEntrega, ?int $productoId, ?int $tipoEnvioId): ?array
    {
        if (!$fechaEntrega) {
            return null;
        }

        $fechaEntregaDate = Carbon::parse($fechaEntrega)->startOfDay();
        [$diasProduccion, $diasEnvio] = $this->resolveLeadTimes($productoId, $tipoEnvioId);
        $fechaEmbarque = $this->subtractBusinessDays($fechaEntregaDate, $diasEnvio);
        $fechaProduccion = $this->subtractBusinessDays($fechaEmbarque, $diasProduccion);

        return [
            'fecha_embarque' => $fechaEmbarque->format('Y-m-d'),
            'fecha_produccion' => $fechaProduccion->format('Y-m-d'),
            'mensaje_produccion' => $fechaProduccion->lt(Carbon::now()->startOfDay())
                ? "⚠️ La fecha de producción calculada ({$fechaProduccion->format('Y-m-d')}) ya ha pasado. Se requiere autorización adicional."
                : null,
        ];
    }

    public function adjustToWeekday(?string $fecha): ?string
    {
        if (!$fecha) {
            return $fecha;
        }

        $date = Carbon::parse($fecha);

        if ($date->dayOfWeek === Carbon::SATURDAY) {
            $date->addDays(2);
        } elseif ($date->dayOfWeek === Carbon::SUNDAY) {
            $date->addDay();
        }

        return $date->format('Y-m-d');
    }

    protected function resolveLeadTimes(?int $productoId, ?int $tipoEnvioId): array
    {
        $diasProduccion = (int) (Producto::find($productoId)?->dias_produccion ?? 6);
        $diasEnvio = (int) (TipoEnvio::find($tipoEnvioId)?->dias_envio ?? 2);

        return [$diasProduccion, $diasEnvio];
    }

    protected function subtractBusinessDays(Carbon $fecha, int $dias): Carbon
    {
        $fecha = $fecha->copy();
        $contador = 0;

        while ($contador < $dias) {
            $fecha->subDay();
            if ($fecha->isWeekday()) {
                $contador++;
            }
        }

        return $fecha;
    }
}
