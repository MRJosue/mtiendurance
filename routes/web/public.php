<?php

use App\Events\MessageSent;
use App\Events\NewChatMessage;
use App\Events\TestReverbMessage;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoController;
use App\Models\MensajeChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

Route::view('/reverb-test', 'reverb-test')->name('reverb.test');

Route::post('/reverb-test/send', function (Request $request) {
    $data = $request->validate([
        'message' => ['required', 'string', 'max:500'],
    ]);

    broadcast(new TestReverbMessage(
        message: $data['message'],
        sentAt: now()->toDateTimeString()
    ));

    return response()->json(['ok' => true]);
})->name('reverb.test.send');

Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['es', 'en']), 404);

    session(['locale' => $locale]);
    Cookie::queue('locale', $locale, 60 * 24 * 365);

    if (auth()->check() && \Schema::hasColumn('users', 'locale')) {
        auth()->user()->forceFill(['locale' => $locale])->save();
    }

    return back();
})->name('lang.switch');

Route::get('/notificacion', [DemoController::class, 'mostrarNotificacion']);

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'perfil.configurado'])
    ->name('dashboard');

Route::get('/MessageSent', function () {
    event(new MessageSent('¡Hola desde el servidor!'));

    return 'Evento emitido.';
});

Route::get('/emitir-demo', function () {
    broadcast(new class('Hola desde canal-demo') implements \Illuminate\Contracts\Broadcasting\ShouldBroadcastNow {
        public $mensaje;

        public function __construct($mensaje)
        {
            $this->mensaje = $mensaje;
        }

        public function broadcastOn()
        {
            return new \Illuminate\Broadcasting\Channel('canal-demo');
        }

        public function broadcastAs()
        {
            return 'evento.demo';
        }
    });

    return 'Evento emitido.';
});

Route::view('/demo', 'demo');

Route::get('/ChatMessageTest', function () {
    $mensaje = MensajeChat::create([
        'chat_id' => 4,
        'usuario_id' => 1,
        'mensaje' => 'Este es un mensaje de prueba desde Tinker.',
    ]);

    event(new NewChatMessage($mensaje));

    return 'Evento Chat emitido.';
});
