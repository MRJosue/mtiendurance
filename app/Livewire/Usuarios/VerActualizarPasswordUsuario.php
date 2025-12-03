<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Str;

class VerActualizarPasswordUsuario extends Component
{
    public User $user;

    public string $new_password = '';
    public string $new_password_confirmation = '';
    public ?string $generatedPassword = null;

    public function mount(User $user)
    {
        $this->user = $user;
    }

    protected function rules(): array
    {
        return [
            'new_password'              => ['required', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required', 'string', 'min:8'],
        ];
    }

    public function generateRandomPassword(): void
    {
        // Laravel 10 tiene Str::password(); si no, puedes usar Str::random()
        $password = Str::password(12);

        $this->generatedPassword           = $password;
        $this->new_password                = $password;
        $this->new_password_confirmation   = $password;
    }

    public function updatePassword(): void
    {
        $this->validate();

        // Tienes cast 'password' => 'hashed' en el modelo,
        // así que al asignar el string, Laravel lo hashea automáticamente.
        $this->user->password = $this->new_password;
        $this->user->save();

        // Limpiar campos
        $this->reset(['new_password', 'new_password_confirmation']);

        // Notificación para el front
        $this->dispatch(
            'password-actualizada',
            id: $this->user->id
        );

        session()->flash('password_message', 'La contraseña se actualizó correctamente.');
    }

    public function render()
    {
        return view('livewire.usuarios.ver-actualizar-password-usuario');
    }
}


// class VerActualizarPasswordUsuario extends Component
// {
//     public function render()
//     {
//         return view('livewire.usuarios.ver-actualizar-password-usuario');
//     }
// }
