<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class ArchivoPedido extends Model
{
    use HasFactory;

    protected $table = 'archivos_pedido';

    protected $fillable = [
        'pedido_id',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_archivo',
        'tipo_carga',
        'flag_descarga',
        'usuario_id',
        'descripcion',
        'version',
    ];

    /** Relaciones */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /** URL pública normalizada del archivo (verimagen) */
    public function getVerimagenAttribute(): ?string
    {
        if (!$this->ruta_archivo) return null;

        $path = ltrim($this->ruta_archivo, '/');
        return Storage::disk('public')->url($path);
    }

    /** Saber si es imagen */
    public function getEsImagenAttribute(): bool
    {
        $mime = $this->tipo_archivo ?? '';
        return str_starts_with($mime, 'image/');
    }


    
    /** Descargar archivo */
    public function descargar()
    {
        if (! Storage::disk('public')->exists($this->ruta_archivo)) {
            throw new \Exception('El archivo no existe en el servidor.');
        }

        return Storage::disk('public')
            ->download($this->ruta_archivo, $this->nombre_archivo);
    }
}
