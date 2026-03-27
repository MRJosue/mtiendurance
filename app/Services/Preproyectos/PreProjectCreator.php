<?php

namespace App\Services\Preproyectos;

use App\Models\ArchivoProyecto;
use App\Models\Categoria;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\PreProyecto;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;

class PreProjectCreator
{
    public function create(array $data): PreProyecto
    {
        $categoria = Categoria::find($data['categoria_id']);
        $producto = Producto::find($data['producto_id']);

        return PreProyecto::create([
            'usuario_id' => $data['selected_user_id'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'tipo' => 'PROYECTO',
            'numero_muestras' => 0,
            'estado' => 'PENDIENTE',
            'fecha_produccion' => $data['fecha_produccion'],
            'fecha_embarque' => $data['fecha_embarque'],
            'fecha_entrega' => $data['fecha_entrega'],
            'flag_armado' => $data['seleccion_armado'],
            'flag_requiere_proveedor' => $data['flag_requiere_proveedor'],
            'categoria_sel' => json_encode(['id' => $data['categoria_id'], 'nombre' => $categoria?->nombre]),
            'producto_sel' => json_encode(['id' => $data['producto_id'], 'nombre' => $producto?->nombre]),
            'caracteristicas_sel' => json_encode($data['caracteristicas_sel']),
            'opciones_sel' => json_encode($data['opciones_sel']),
            'direccion_entrega_id' => $data['direccion_entrega_id'],
            'direccion_entrega' => $this->resolveAddressSummary(DireccionEntrega::class, $data['direccion_entrega_id']),
            'direccion_fiscal_id' => $data['direccion_fiscal_id'],
            'direccion_fiscal' => $this->resolveAddressSummary(DireccionFiscal::class, $data['direccion_fiscal_id']),
            'id_tipo_envio' => $data['id_tipo_envio'],
            'total_piezas_sel' => json_encode([
                'total' => (int) $data['total_piezas'],
                'detalle_tallas' => $data['detalle_tallas'],
            ]),
            'cliente_id' => null,
        ]);
    }

    public function storeUploadedFiles(PreProyecto $preProyecto, array $files, array $fileDescriptions): void
    {
        foreach ($files as $index => $file) {
            $path = $file->store('archivos_proyectos', 'public');

            ArchivoProyecto::create([
                'pre_proyecto_id' => $preProyecto->id,
                'usuario_id' => Auth::id(),
                'nombre_archivo' => $file->getClientOriginalName(),
                'ruta_archivo' => $path,
                'tipo_archivo' => $file->getClientMimeType(),
                'descripcion' => $fileDescriptions[$index] ?? '',
                'tipo_carga' => 2,
                'log' => [
                    '0' => [
                        'ip' => request()->ip(),
                        'fecha' => now()->format('Y-m-d H:i:s'),
                        'accion' => 'Cargado',
                        'usuario_id' => Auth::id(),
                        'flag_descarga_antes' => 0,
                        'flag_descarga_despues' => 0,
                    ],
                ],
            ]);
        }
    }

    protected function resolveAddressSummary(string $addressModel, ?int $addressId): string
    {
        $address = $addressModel::with(['estado.pais', 'pais'])->find($addressId);

        return trim(implode(', ', array_filter([
            $address?->ciudad,
            $address?->estado?->nombre,
            $address?->pais?->nombre,
        ])), ' ,');
    }
}
