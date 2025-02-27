<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClienteManagement extends Component
{
    public $clientes;
    public $clienteId;
    public $nombre_empresa;
    public $contacto_principal;
    public $telefono;
    public $email;
    public $modalOpen = false;
    public $userId;

    protected function rules()
    {
        return [
            'nombre_empresa' => 'required|string|max:255',
            'contacto_principal' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:15',
            'email' => 'required|email|unique:clientes,email,' . $this->clienteId,
        ];
    }

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->clientes = Cliente::where('usuario_id', $userId)->get();
    }

    public function create()
    {
        $this->reset(['clienteId', 'nombre_empresa', 'contacto_principal', 'telefono', 'email']);
        $this->modalOpen = true;
    }

    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);
        $this->clienteId = $cliente->id;
        $this->nombre_empresa = $cliente->nombre_empresa;
        $this->contacto_principal = $cliente->contacto_principal;
        $this->telefono = $cliente->telefono;
        $this->email = $cliente->email;
        $this->modalOpen = true;
    }

    public function save()
    {

        Log::debug('validate pre', ['fecha_entrega' =>$this->clienteId ]);
        $this->validate($this->rules());
        Log::debug('userId pre upd/crate', ['fecha_entrega' =>$this->clienteId ]);
        Cliente::updateOrCreate(

            ['id' => $this->clienteId],
            [
                'usuario_id' => $this->userId, // Asigna siempre el usuario
                'nombre_empresa' => $this->nombre_empresa,
                'contacto_principal' => $this->contacto_principal,
                'telefono' => $this->telefono,
                'email' => $this->email,
            ]
        );

        Log::debug('userId pre close', ['data' => $this->userId]);

        $this->modalOpen = false;
        $this->clientes = Cliente::where('usuario_id', $this->userId)->get();
    }

    public function delete($id)
    {
        Cliente::findOrFail($id)->delete();
        $this->clientes = Cliente::where('usuario_id', $this->userId)->get();
    }

    public function render()
    {
        return view('livewire.usuarios.cliente-management', [
            'clientes' => $this->clientes
        ]);
    }
}



// return view('livewire.usuarios.cliente-management');