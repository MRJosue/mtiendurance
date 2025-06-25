<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;
use App\Models\Proyecto;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfAprobacion extends Component
{
    public int $proyectoId;

    public function mount(int $proyectoId): void
    {
        $this->proyectoId = $proyectoId;
    }

public function descargar()
{
    $proyecto = Proyecto::with(['estados.usuario', 'archivos'])
        ->findOrFail($this->proyectoId);

    $registro = $proyecto->estados
        ->where('estado', 'DISEÑO APROBADO')
        ->sortByDesc('id')
        ->first();

    // ➤ Renombramos a $archivo
    $archivo = $proyecto->archivos
        ->where('tipo_carga', 1)
        ->sortByDesc('id')
        ->first();

    // Calculamos la ruta física en disco
    $rutaArchivo = null;
    if ($archivo) {
        $diskPath = storage_path('app/public/' . $archivo->ruta_archivo);
        $rutaArchivo = file_exists($diskPath)
            ? $diskPath
            : null;
    }

    // Pasamos “archivo” (antes “ultimoArchivo”)
    $pdf = Pdf::loadView('pdf.aprobacion', compact('proyecto', 'registro', 'archivo', 'rutaArchivo'));

    return response()->streamDownload(
        fn() => print($pdf->output()),
        "aprobación_proyecto_{$this->proyectoId}.pdf"
    );
}

    public function render()
    {
        return view('livewire.proyectos.pdf-aprobacion');
    }
}