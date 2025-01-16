<?php

namespace App\Livewire;


use Livewire\Component;
use App\Models\MensajeChat;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use App\Events\NewChatMessage;

class ChatComponent extends Component
{
    public $proyectoId;
    public $chatId;
    public $mensajes = [];
    public $mensaje;

    protected $rules = [
        'mensaje' => 'required|string|max:500',
    ];

    protected $listeners = ['actualizarMensajes'];

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;

        // Obtener el chat asociado al proyecto
        $chat = Chat::where('proyecto_id', $this->proyectoId)->first();

        if (!$chat) {
            abort(404, 'Chat no encontrado para este proyecto.');
        }

        $this->chatId = $chat->id;

        // Cargar los mensajes iniciales
        $this->loadMensajes();
    }

    public function enviarMensaje()
    {
        $this->validate();

        // Crear un nuevo mensaje en el chat
        $mensaje = MensajeChat::create([
            'chat_id' => $this->chatId,
            'usuario_id' => Auth::id(),
            'mensaje' => $this->mensaje,
        ]);

        // Emitir el evento de nuevo mensaje
        event(new NewChatMessage($mensaje));

        // Limpiar el campo de entrada después de enviar
        $this->mensaje = '';

        // Opcional: Recargar mensajes si deseas una actualización inmediata localmente
        $this->loadMensajes();
    }

    public function loadMensajes()
    {
        $this->mensajes = MensajeChat::where('chat_id', $this->chatId)
            ->with('usuario') // Cargar información del usuario
            ->orderBy('fecha_envio', 'asc')
            ->get()
            ->toArray(); // Convertir a array para evitar problemas con Livewire
    }

    public function actualizarMensajes()
    {
        // Cargar mensajes nuevamente al recibir el evento
        $this->loadMensajes();
    }

    public function render()
    {
        return view('livewire.chat-component');
    }
}