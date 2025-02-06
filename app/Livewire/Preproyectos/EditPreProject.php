<?php

namespace App\Livewire\Preproyectos;


use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\PreProyecto;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\Ciudad;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\TipoEnvio;
use App\Models\ArchivoProyecto;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Caracteristica;
use App\Models\Talla;
use App\Models\Opcion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EditPreProject extends Component
{
    use WithFileUploads;

    public $preProyectoId;
    public $nombre;
    public $descripcion;
    public $fecha_produccion;
    public $fecha_embarque;
    public $fecha_entrega;

    public $categoria_id;
    public $producto_id;
    public $productos = [];
    public $caracteristicas_sel = [];
    public $opciones_sel = [];

    public $total_piezas;
    public $tallas = [];
    public $tallasSeleccionadas = [];
    public $mostrarFormularioTallas = false;

    public $direccion_fiscal;
    public $direccion_entrega;
    public $id_tipo_envio;
    public $tipos_envio = [];

    public $mensaje_produccion;

    // Archivos
    public $files = [];
    public $fileDescriptions = [];
    public $uploadedFiles = [];
    public $existingFiles = [];

    public function mount($preProyectoId)
    {
        $this->preProyectoId = $preProyectoId;
        

        $preProyecto = PreProyecto::findOrFail($preProyectoId);

        $this->nombre = $preProyecto->nombre;
        $this->descripcion = $preProyecto->descripcion;

        $this->fecha_produccion = Carbon::parse($preProyecto->fecha_produccion)->format('Y-m-d');
         Log::info('Este es un mensaje de información.'. $this->fecha_produccion);  

        $this->fecha_embarque = Carbon::parse($preProyecto->fecha_embarque)->format('Y-m-d');
         Log::info('Este es un mensaje de información.'. $this->fecha_embarque);  

        $this->fecha_entrega = Carbon::parse($preProyecto->fecha_entrega)->format('Y-m-d');
         Log::info('Este es un mensaje de información.'.$this->fecha_entrega);  
         

        $this->categoria_id = json_decode($preProyecto->categoria_sel)->id;
        $this->producto_id = json_decode($preProyecto->producto_sel)->id;
        $this->total_piezas = json_decode($preProyecto->total_piezas_sel)->total ?? 0;
        $this->direccion_fiscal = $preProyecto->direccion_fiscal;
        $this->direccion_entrega = $preProyecto->direccion_entrega;
        $this->id_tipo_envio = $preProyecto->id_tipo_envio;

        // Cargar productos de la categoría seleccionada
        $this->productos = Producto::whereHas('categorias', function ($query) {
            $query->where('categoria_id', $this->categoria_id);
        })->get();

        // Cargar tallas si es "Playeras"
        $categoria = Categoria::find($this->categoria_id);
        $this->mostrarFormularioTallas = $categoria && strtolower($categoria->nombre) === 'playeras';
        $this->tallas = Talla::all();
        $this->tallasSeleccionadas = json_decode($preProyecto->total_piezas_sel)->detalle_tallas ?? [];

        // Cargar archivos existentes
        $this->existingFiles = ArchivoProyecto::where('pre_proyecto_id', $this->preProyectoId)->get();
    }

    public function update()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_entrega' => 'nullable|date',
            'categoria_id' => 'required|exists:categorias,id',
            'producto_id' => 'required|exists:productos,id',
            'direccion_fiscal_id' => 'required',
            'direccion_entrega_id' => 'required',
            'id_tipo_envio' => 'required',
            'total_piezas' => $this->mostrarFormularioTallas ? 'nullable' : 'required|integer|min:1',
            'tallasSeleccionadas' => $this->mostrarFormularioTallas ? 'required|array|min:1' : 'nullable',
        ]);

        $totalPiezasFinal = $this->mostrarFormularioTallas ? array_sum($this->tallasSeleccionadas) : $this->total_piezas;

        // Actualizar el preproyecto
        $preProyecto = PreProyecto::findOrFail($this->preProyectoId);
        $preProyecto->update([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'fecha_entrega' => $this->fecha_entrega,
            'categoria_sel' => json_encode(['id' => $this->categoria_id]),
            'producto_sel' => json_encode(['id' => $this->producto_id]),
            'total_piezas_sel' => json_encode([
                'total' => $totalPiezasFinal,
                'detalle_tallas' => $this->mostrarFormularioTallas ? $this->tallasSeleccionadas : null
            ]),
        ]);

        // Guardar nuevos archivos
        foreach ($this->files as $index => $file) {
            $path = $file->store('archivos_proyectos', 'public');
            ArchivoProyecto::create([
                'pre_proyecto_id' => $this->preProyectoId,
                'usuario_id' => Auth::id(),
                'nombre_archivo' => $file->getClientOriginalName(),
                'ruta_archivo' => $path,
                'tipo_archivo' => $file->getClientMimeType(),
                'descripcion' => $this->fileDescriptions[$index] ?? '',
            ]);
        }

        session()->flash('message', 'Preproyecto actualizado exitosamente.');
        return redirect()->route('preproyectos.index');
    }

    public function deleteFile($fileId)
    {
        $file = ArchivoProyecto::findOrFail($fileId);
        Storage::disk('public')->delete($file->ruta_archivo);
        $file->delete();

        $this->existingFiles = ArchivoProyecto::where('pre_proyecto_id', $this->preProyectoId)->get();
    }

    public function render()
    {
        return view('livewire.preproyectos.edit-pre-project', [
            'categorias' => Categoria::all(),
            'productos' => $this->productos,
            'tiposEnvio' => TipoEnvio::all(),
        ]);
    }


    public function setReadOnlyMode()
    {
        $this->dispatch('setReadOnlyMode');
    }
}



//return view('livewire.preproyectos.edit-pre-project');