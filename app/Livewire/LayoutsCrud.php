<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;


use App\Models\Layout;
use App\Models\LayoutElemento;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Caracteristica;


use Illuminate\Support\Facades\Auth;


class LayoutsCrud extends Component
{
    public $layout_id;
    public $nombre, $descripcion, $producto_id, $categoria_id;
    public $layouts = [];
    public $modoEdicion = false;

    public $elementos = [];

    use WithFileUploads;
    public $imagenesTemp = []; // clave: índice, valor: instancia temporal de Livewire\TemporaryUploadedFile



    public function mount()
    {
        $this->layouts = Layout::where('usuario_id', Auth::id())->get();
    }

    public function render()
    {
        return view('livewire.layouts-crud', [
            'productos' => Producto::all(),
            'categorias' => Categoria::all(),
            'caracteristicas' => Caracteristica::all(),
        ]);
    }

    public function crear()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'producto_id' => 'nullable|exists:productos,id',
            'categoria_id' => 'nullable|exists:categorias,id',
        ]);

        Layout::create([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'producto_id' => $this->producto_id,
            'categoria_id' => $this->categoria_id,
            'usuario_id' => Auth::id(),
        ]);

        $this->resetCampos();
        $this->layouts = Layout::where('usuario_id', Auth::id())->get();
    }

    public function editar($id)
    {
        $layout = Layout::findOrFail($id);
        $this->layout_id = $layout->id;
        $this->nombre = $layout->nombre;
        $this->descripcion = $layout->descripcion;
        $this->producto_id = $layout->producto_id;
        $this->categoria_id = $layout->categoria_id;
        $this->modoEdicion = true;

        $this->elementos = LayoutElemento::where('layout_id', $id)->get()->toArray();
    }

    public function guardarElementos()
    {
        if (!$this->layout_id) return;

        $letras = [];

        foreach ($this->elementos as $e) {
            $letra = strtoupper(trim($e['letra'] ?? ''));
            if ($letra !== '') {
                if (in_array($letra, $letras)) {
                    session()->flash('error', "La letra '{$letra}' está repetida. Cada elemento debe tener una letra única.");
                    return;
                }
                $letras[] = $letra;
            }
        }

        LayoutElemento::where('layout_id', $this->layout_id)->delete();

        foreach ($this->elementos as $index => $elemento) {
            $rutaImagen = null;
        
            if ($elemento['tipo'] === 'imagen' && isset($this->imagenesTemp[$index])) {
                $rutaImagen = $this->imagenesTemp[$index]->store('public/layouts');
            }
        
            LayoutElemento::create([
                'layout_id' => $this->layout_id,
                'tipo' => $elemento['tipo'],
                'caracteristica_id' => $elemento['caracteristica_id'] ?? null,
                'letra' => strtoupper(trim($elemento['letra'] ?? null)),
                'posicion_x' => $elemento['posicion_x'] ?? 0,
                'posicion_y' => $elemento['posicion_y'] ?? 0,
                'ancho' => $elemento['ancho'] ?? 100,
                'alto' => $elemento['alto'] ?? 100,
                'orden' => $elemento['orden'] ?? 0,
                'configuracion' => [
                    'url' => $rutaImagen ? str_replace('public/', 'storage/', $rutaImagen) : ($elemento['configuracion']['url'] ?? null)
                ],
            ]);
        }
        session()->flash('message', 'Elementos del layout guardados.');
    }

    public function addElemento()
    {
        $letrasUsadas = collect($this->elementos)->pluck('letra')->filter()->map(fn($l) => strtoupper(trim($l)))->values();
        $letraNueva = $this->generarLetraDisponible($letrasUsadas);

        $this->elementos[] = [
            'tipo' => 'caracteristica',
            'caracteristica_id' => null,
            'letra' => $letraNueva,
            'posicion_x' => 10,
            'posicion_y' => 10,
            'ancho' => 100,
            'alto' => 100,
            'orden' => count($this->elementos),
            'configuracion' => [],
        ];
    }

    public function generarLetraDisponible($letrasUsadas)
    {
        foreach (range('A', 'Z') as $letra) {
            if (!$letrasUsadas->contains($letra)) {
                return $letra;
            }
        }

        $i = 1;
        while (true) {
            $letra = 'X' . $i;
            if (!$letrasUsadas->contains($letra)) return $letra;
            $i++;
        }
    }

    public function cambiarOrden($index, $delta)
    {
        $actual = $this->elementos[$index]['orden'] ?? 0;
        $nuevo = $actual + $delta;
        $this->elementos[$index]['orden'] = max(0, $nuevo);
    }


    public function resetCampos()
    {
        $this->reset(['nombre', 'descripcion', 'producto_id', 'categoria_id', 'modoEdicion', 'layout_id', 'elementos']);
    }
}