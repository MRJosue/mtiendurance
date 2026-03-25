<?php

use App\Http\Controllers\ProfileController;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/perfil/configuracion-inicial', function () {
        return view('perfil.configuracion-inicial', ['user' => auth()->user()]);
    })->name('perfil.inicial');

    Route::post('/perfil/configuracion-inicial/finalizar', function () {
        $user = auth()->user();

        $tieneFiscal = DireccionFiscal::where('usuario_id', $user->id)->exists();
        $tieneEntrega = DireccionEntrega::where('usuario_id', $user->id)->exists();

        if (! $tieneFiscal || ! $tieneEntrega) {
            return back()->withErrors([
                'direcciones' => 'Debes registrar al menos una dirección fiscal y una de entrega.',
            ]);
        }

        $rfc = $user->config['rfc'] ?? null;
        if (! $rfc) {
            return back()->withErrors([
                'direcciones' => 'Falta el RFC en tus datos de usuario.',
            ]);
        }

        $user->update(['flag_perfil_configurado' => true]);

        return redirect()->route('dashboard');
    })->name('perfil.inicial.finalizar');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
