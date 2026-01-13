<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PerfilDatosUsuario extends Component
{
    public int $userId;

    public string $name = '';
    public string $email = '';
    public string $rfc = '';

    public string $password = '';
    public string $password_confirmation = '';

    public function mount(int $userId): void
    {
        $this->userId = $userId;

        $u = Auth::user();

        // Seguridad: evitar que editen otro usuario aquí
        abort_if($u->id !== $this->userId, 403);

        $this->name  = $u->name ?? '';
        $this->email = $u->email ?? '';
        $this->rfc   = $u->config['rfc'] ?? '';
    }

    public function rules(): array
    {
        return [
            // 'name'  => ['required','string','max:255'],
            // 'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($this->userId)],
            'rfc'   => ['required','string','max:13'],

            // Password opcional
            'password' => ['nullable','string','min:8','same:password_confirmation'],
            'password_confirmation' => ['nullable','string','min:8'],
        ];
    }

    public function guardar(): void
    {
        $this->validate();

        $u = Auth::user();
        abort_if($u->id !== $this->userId, 403);

        // $u->name  = $this->name;
        // $u->email = $this->email;

        $cfg = $u->config ?? [];
        $cfg['rfc'] = strtoupper(trim($this->rfc));
        $u->config = $cfg;

        if (!empty($this->password)) {
            $u->password = Hash::make($this->password);
        }

        $u->save();

        // Livewire v3 -> dispatch
        $this->dispatch('notify', [
            'title' => 'Guardado',
            'description' => 'Datos de usuario actualizados.',
            'icon' => 'success',
        ]);
    }

    public function render()
    {
        return view('livewire.usuarios.perfil-datos-usuario');
    }
}
