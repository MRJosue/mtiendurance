<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\MensajeChat;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatProveedorComponent extends Component
{
    public int $proyectoId;
    public ?int $proveedorId = null;
    public ?int $chatId = null;

    public array $mensajes = [];
    public string $mensaje = '';

    protected $rules = [
        'mensaje' => 'required|string|max:500',
    ];

    protected $listeners = ['actualizarMensajes'];

    /**
     * Recibe el ID de proyecto y opcionalmente el ID de proveedor.
     * Si no se especifica proveedor, se usa el usuario autenticado.
     */
    public function mount(int $proyectoId, ?int $proveedorId = null): void
    {
        Log::info('Mount ChatProveedorComponent', [
            'proyectoId'  => $proyectoId,
            'proveedorId' => $proveedorId,
        ]);

        $this->proyectoId  = $proyectoId;
        $this->proveedorId = $proveedorId ?: Auth::id();

        // Buscar chat de proveedor para este proyecto y proveedor
        $chat = Chat::where('proyecto_id', $this->proyectoId)
            ->where('tipo_chat', 2) // 2 = proveedor
            ->where('proveedor_id', $this->proveedorId)
            ->first();

        // Si no existe, lo creamos
        if (! $chat) {
            $chat = Chat::create([
                'proyecto_id'    => $this->proyectoId,
                'tipo_chat'      => 2,
                'proveedor_id'   => $this->proveedorId,
                'fecha_creacion' => now(),
            ]);
        }

        $this->chatId = $chat->id;

        $this->loadMensajes();
    }

    public function enviarMensaje(): void
    {
        $this->validate();

        MensajeChat::create([
            'chat_id'    => $this->chatId,
            'usuario_id' => Auth::id(),
            'mensaje'    => $this->mensaje,
        ]);

        // Limpiar input
        $this->mensaje = '';

        // Recargar mensajes
        $this->loadMensajes();

        // Si después quieres usar broadcasting, aquí puedes disparar tu evento:
        // event(new NewChatMessage($mensaje));
    }

    public function loadMensajes(): void
    {
        $this->mensajes = MensajeChat::where('chat_id', $this->chatId)
            ->with('usuario')
            ->orderBy('fecha_envio', 'asc')
            ->get()
            ->toArray();
    }

    public function actualizarMensajes(): void
    {
        $this->loadMensajes();
    }

    public function render()
    {
        return view('livewire.chat-proveedor-component');
    }
}

// namespace App\Livewire;

// use Livewire\Component;

// class ChatProveedorComponent extends Component
// {
//     public function render()
//     {
//         return view('livewire.chat-proveedor-component');
//     }
// }
