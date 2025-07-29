<?php



namespace App\Livewire\Proyectos;

use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Models\ArchivoProyecto;

class LigaArchivoAprobado extends Component
{
    public $proyectoId;
    public $archivoAprobado;
    public $verModal = false;

    protected $listeners = ['archivoSubido' => 'cargarArchivoAprobado'];

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
        $this->cargarArchivoAprobado();
    }

    public function cargarArchivoAprobado()
    {
        $this->archivoAprobado = ArchivoProyecto::where('proyecto_id', $this->proyectoId)
            ->where('tipo_carga', 2)
            ->latest('id')
            ->first();
    }

    public function abrirModal()
    {
        $this->verModal = true;
    }

    public function cerrarModal()
    {
        $this->verModal = false;
    }

    public function render()
    {
        return view('livewire.proyectos.liga-archivo-aprobado');
    }
}
