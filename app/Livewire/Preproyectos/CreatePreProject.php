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
    public $productos = [];
    public $caracteristicas_sel = [];
    public $opciones_sel = [];

    public $caracteristica_id;
    public $direccion_fiscal_id;
    public $direccion_entrega_id;

    public function onCategoriaChange()
    {
        $this->producto_id = null;
        $this->productos = Producto::whereHas('categorias', function ($query) {
            $query->where('categoria_id', $this->categoria_id);
        })->get();

        $this->caracteristicas_sel = [];
        $this->opciones_sel = [];
    }

    public function onProductoChange()
    {
        $this->caracteristica_id = null;
        $this->caracteristicas_sel = Caracteristica::whereHas('productos', function ($query) {
            $query->where('producto_id', $this->producto_id);
        })->get()->map(function ($caracteristica) {
            return [
                'id' => $caracteristica->id,
                'nombre' => $caracteristica->nombre,
                'flag_seleccion_multiple' => $caracteristica->flag_seleccion_multiple,
                'opciones' => []
            ];
        })->toArray();

        $this->opciones_sel = [];
    }

    public function addOpcion($caracteristicaIndex, $opcion_id)
    {
        if (!isset($this->caracteristicas_sel[$caracteristicaIndex])) {
            return;
        }

        $caracteristica = &$this->caracteristicas_sel[$caracteristicaIndex];
        $opcion = Opcion::find($opcion_id);

        if ($opcion) {
            if (!$caracteristica['flag_seleccion_multiple']) {
                // Si la característica no permite selección múltiple, solo se puede elegir una opción
                $caracteristica['opciones'] = [
                    [
                        'id' => $opcion->id,
                        'nombre' => $opcion->nombre,
                        'valoru' => $opcion->valoru,
                    ]
                ];
            } else {
                // Si permite selección múltiple, se pueden agregar más opciones
                if (!in_array($opcion->id, array_column($caracteristica['opciones'], 'id'))) {
                    $caracteristica['opciones'][] = [
                        'id' => $opcion->id,
                        'nombre' => $opcion->nombre,
                        'valoru' => $opcion->valoru,
                    ];
                }
            }
        }
    }

    public function removeOpcion($caracteristicaIndex, $opcionIndex)
    {
        unset($this->caracteristicas_sel[$caracteristicaIndex]['opciones'][$opcionIndex]);
        $this->caracteristicas_sel[$caracteristicaIndex]['opciones'] = array_values($this->caracteristicas_sel[$caracteristicaIndex]['opciones']);
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

        $preProyecto = PreProyecto::create([
            'usuario_id' => Auth::id(),
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'tipo' => 'PROYECTO',
            'numero_muestras' => 0,
            'estado' => 'PENDIENTE',
            'fecha_produccion' => $this->fecha_produccion,
            'fecha_embarque' => $this->fecha_embarque,
            'fecha_entrega' => $this->fecha_entrega,
            'categoria_sel' => json_encode(['id' => $this->categoria_id, 'nombre' => Categoria::find($this->categoria_id)->nombre]),
            'producto_sel' => json_encode(['id' => $this->producto_id, 'nombre' => Producto::find($this->producto_id)->nombre]),
            'caracteristicas_sel' => json_encode($this->caracteristicas_sel),
            'opciones_sel' => json_encode($this->opciones_sel),
        ]);

        session()->flash('message', 'Preproyecto creado exitosamente.');
        return redirect()->route('preproyectos.index');
    }

    public function render()
    {
        return view('livewire.preproyectos.create-pre-project', [
            'categorias' => Categoria::all(),
            'productos' => $this->productos,
            'direccionesFiscales' => DireccionFiscal::where('usuario_id', Auth::id())->get(),
            'direccionesEntrega' => DireccionEntrega::where('usuario_id', Auth::id())->get(),
        ]);
    }
}
