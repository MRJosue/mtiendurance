<?php

namespace App\Livewire\Perfil;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Models\DireccionFiscal;
use App\Models\DireccionEntrega;

class ConfiguracionInicial extends Component
{
    public $name;
    public $email;

    // RFC (lo guardamos en config para no tocar DB)
    public $rfc;

    // Password opcional
    public $password = '';
    public $password_confirmation = '';

    public function mount(): void
    {
        $u = Auth::user();

        $this->name  = $u->name;
        $this->email = $u->email;

        // RFC desde config (ajusta la key si quieres)
        $this->rfc = $u->config['rfc'] ?? '';
    }

    public function rules(): array
    {
        return [
            'name'  => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore(Auth::id())],

            'rfc'   => ['required','string','max:13'],

            // password opcional: si escribe algo, validamos confirmación
            'password' => ['nullable','string','min:8','same:password_confirmation'],
            'password_confirmation' => ['nullable','string','min:8'],
        ];
    }

    public function guardar(): void
    {
        $this->validate();

        $u = Auth::user();

        $u->name  = $this->name;
        $u->email = $this->email;

        // Guardar RFC en config
        $cfg = $u->config ?? [];
        $cfg['rfc'] = strtoupper(trim($this->rfc));
        $u->config = $cfg;

        // Password opcional
        if (!empty($this->password)) {
            $u->password = Hash::make($this->password);
        }

        $u->save();

        // Validar que tenga direcciones (y si quieres, default)
        $tieneFiscal = DireccionFiscal::where('usuario_id', $u->id)->exists();
        $tieneEntrega = DireccionEntrega::where('usuario_id', $u->id)->exists();

        if (!$tieneFiscal || !$tieneEntrega) {
            $faltan = [];
            if (!$tieneFiscal) $faltan[] = 'Dirección fiscal';
            if (!$tieneEntrega) $faltan[] = 'Dirección de entrega';

            $this->dispatch('notify', [
                'title' => 'Faltan datos',
                'description' => 'Completa: '.implode(' y ', $faltan).'.',
                'icon' => 'error',
            ]);

            return;
        }

        // Marcar perfil configurado
        $u->update(['flag_perfil_configurado' => true]);

        // Redirigir a dashboard
        redirect()->route('dashboard')->send();
    }

    public function render()
    {
        return view('livewire.perfil.configuracion-inicial');
    }
}