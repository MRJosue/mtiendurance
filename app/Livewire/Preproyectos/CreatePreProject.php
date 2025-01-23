<?php

namespace App\Livewire\Preproyectos;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\PreProyecto;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\ArchivoProyecto;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Caracteristica;
use App\Models\Opcion;
use Illuminate\Support\Facades\Auth;

class CreatePreProject extends Component
{
    use WithFileUploads;

    public $nombre;
    public $descripcion;
    public $fecha_produccion;
    public $fecha_embarque;
    public $fecha_entrega;
    public $files = [];
    public $fileDescriptions = [];

    public $categoria_id;
    public $producto_id;
    public $productos;
    public $caracteristicas = [];
    public $caracteristica_id;
    public $opciones = [];
    public $opcion_id;

    public $direccion_fiscal_id;
    public $direccion_entrega_id;

    public $categoria_sel;
    public $producto_sel;
    public $caracteristicas_sel = [];
    public $opciones_sel = [];

    public function onCategoriaChange()
    {
        $this->producto_id = null;
        $this->productos = $this->categoria_id
            ? Producto::where('categoria_id', $this->categoria_id)->get()
            : [];
        $this->caracteristicas = [];
        $this->caracteristicas_sel = [];
        $this->opciones_sel = [];
    }

    public function onProductoChange()
    {
        $this->caracteristica_id = null;
        $this->caracteristicas = $this->producto_id
            ? Caracteristica::where('producto_id', $this->producto_id)->get()
            : [];
        $this->caracteristicas_sel = [];
        $this->opciones_sel = [];
    }

    public function addCaracteristica()
    {
        $caracteristica = Caracteristica::find($this->caracteristica_id);
        if ($caracteristica && !in_array($caracteristica, $this->caracteristicas_sel)) {
            $this->caracteristicas_sel[] = $caracteristica;
        }
    }

    public function removeCaracteristica($index)
    {
        unset($this->caracteristicas_sel[$index]);
        $this->caracteristicas_sel = array_values($this->caracteristicas_sel);
    }

    public function addOpcion($caracteristicaIndex)
    {
        $caracteristica = $this->caracteristicas_sel[$caracteristicaIndex] ?? null;
        if ($caracteristica) {
            $opcion = Opcion::find($this->opcion_id);
            if ($opcion && !isset($this->opciones_sel[$caracteristica->id])) {
                $this->opciones_sel[$caracteristica->id] = [];
            }
            if ($opcion && !in_array($opcion, $this->opciones_sel[$caracteristica->id])) {
                $this->opciones_sel[$caracteristica->id][] = $opcion;
            }
        }
    }

    public function removeOpcion($caracteristicaId, $opcionIndex)
    {
        unset($this->opciones_sel[$caracteristicaId][$opcionIndex]);
        $this->opciones_sel[$caracteristicaId] = array_values($this->opciones_sel[$caracteristicaId]);
    }

    public function create()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_produccion' => 'nullable|date',
            'fecha_embarque' => 'nullable|date',
            'fecha_entrega' => 'nullable|date',
            'categoria_id' => 'required|exists:categorias,id',
            'producto_id' => 'required|exists:productos,id',
            'direccion_fiscal_id' => 'required|exists:direcciones_entrega,id',
            'direccion_entrega_id' => 'required|exists:direcciones_entrega,id',
        ]);

        $user = Auth::user();
        $direccionFiscal = DireccionFiscal::find($this->direccion_fiscal_id);
        $direccionEntrega = DireccionEntrega::find($this->direccion_entrega_id);

        $direccionConcentrada = "{$direccionFiscal->nombre_contacto}, {$direccionFiscal->calle} | " .
                                "{$direccionEntrega->nombre_contacto}, {$direccionEntrega->calle}";

        $preProyecto = PreProyecto::create([
            'usuario_id' => $user->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'direccion_fiscal' => $direccionFiscal->calle,
            'direccion_entrega' => $direccionEntrega->calle,
            'direccion_concentrada' => $direccionConcentrada,
            'fecha_produccion' => $this->fecha_produccion,
            'fecha_embarque' => $this->fecha_embarque,
            'fecha_entrega' => $this->fecha_entrega,
        ]);


        $auxiliar_preProyecto_id = $preProyecto->id;

        // Crear Archivos
        foreach ($this->files as $index => $file) {
            $rutaArchivo = $file->store('preproyectos/archivos', 'public');
            ArchivoProyecto::create([
                'pre_proyecto_id' => $auxiliar_preProyecto_id,
                'nombre_archivo' => $file->getClientOriginalName(),
                'ruta_archivo' => $rutaArchivo,
                'tipo_archivo' => $file->getMimeType(),
                'usuario_id' => Auth::id(),
                'descripcion' => $this->fileDescriptions[$index] ?? null,
            ]);
        }



        session()->flash('message', 'Preproyecto creado exitosamente.');
        return redirect()->route('preproyectos.index');
    }

    public function render()
    {
        return view('livewire.preproyectos.create-pre-project', [
            'categorias' => Categoria::all(),
            'productos' => $this->categoria_id ? Producto::where('categoria_id', $this->categoria_id)->get() : [],
            'caracteristicasDisponibles' => $this->producto_id ? Caracteristica::where('producto_id', $this->producto_id)->get() : [],
            'direccionesFiscales' => DireccionFiscal::where('usuario_id', Auth::id())->get(),
            'direccionesEntrega' => DireccionEntrega::where('usuario_id', Auth::id())->get(),
        ]);
    }
}
