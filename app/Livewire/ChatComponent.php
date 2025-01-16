<?php

namespace App\Livewire;

use Livewire\Component;



use App\Models\Chat;
use App\Models\MensajeChat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Events\NewChatMessage;

class ChatComponent extends Component
{
    public $proyectoId;
    public $chatId;
    public $mensaje;

    protected $rules = [
        'mensaje' => 'required|string|max:500',
    ];

    protected $listeners = ['mensajeRecibido' => 'actualizarMensajes'];


    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;

        // Obtener el chat asociado al proyecto
        $chat = Chat::where('proyecto_id', $this->proyectoId)->first();

        if (!$chat) {
            abort(404, 'Chat no encontrado para este proyecto.');
        }

        $this->chatId = $chat->id;
    }

    public function enviarMensaje()
    {
        $this->validate();
    
        // Crear un nuevo mensaje en el chat y asignarlo a $mensaje
        $mensaje = MensajeChat::create([
            'chat_id' => $this->chatId,   // ID del chat asociado
            'usuario_id' => Auth::id(),  // Usuario actual
            'mensaje' => $this->mensaje, // Contenido del mensaje
        ]);
    
        // Emitir el evento con la instancia del mensaje
        broadcast(new NewChatMessage($mensaje))->toOthers();
      
        // Limpiar el campo de entrada después de enviar
        $this->mensaje = '';
        $this->render();
    }


    public function actualizarMensajes()
        {
            // Refrescar los mensajes cargados
            dump('actualizarMensajes');
            $this->render();
        }

    public function render()
    {
        // Obtener los mensajes del chat en tiempo real
      
        $mensajes = MensajeChat::where('chat_id', $this->chatId)
            ->with('usuario') // Cargar información del usuario
            ->orderBy('fecha_envio', 'asc')
            ->get();

        return view('livewire.chat-component', [
            'mensajes' => $mensajes,
        ]);
    }
}

