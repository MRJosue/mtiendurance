<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ArchivoProyecto extends Model
{
    use HasFactory;

    protected $table = 'archivos_proyecto';
    protected $fillable = [
        'proyecto_id',
        'pre_proyecto_id',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_archivo',
        'tipo_carga',
        'flag_descarga',
        'usuario_id',
        'descripcion',
        'version',
    ];


    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function preproyecto()
    {
        return $this->belongsTo(preproyecto::class, 'pre_proyecto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

        /**
     * Calcula la siguiente versión para un nuevo archivo
     * basado en archivos previos del mismo proyecto con tipo_carga = 1.
     *
     * @param  int  $proyectoId
     * @return int
     */
    public static function calcularVersion(int $proyectoId): int
    {
        $conteo = self::where('proyecto_id', $proyectoId)
            ->where('tipo_carga', 1)
            ->count();

        return $conteo + 1;
    }

    /**
     * Al crear un nuevo registro, asigna automáticamente
     * la versión siguiente si no se ha proporcionado.
     */
    protected static function booted()
    {
            static::creating(function (ArchivoProyecto $model) {
                if ($model->tipo_carga === 1) {
                    // Le pasamos el proyecto actual para que calcule bien la versión
                    $model->version = self::calcularVersion($model->proyecto_id);
                } else {
                    $model->version = 0;
                }
            });
    }


        /**
     * Genera la respuesta de descarga del archivo.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Exception
     */
    public function descargar()
    {
        // Verificar que el archivo exista en el disco público
        if (! Storage::disk('public')->exists($this->ruta_archivo)) {
            throw new \Exception('El archivo no existe en el servidor.');
        }

        // Devolver la descarga con el nombre original
        return Storage::disk('public')
            ->download($this->ruta_archivo, $this->nombre_archivo);
    }


}
